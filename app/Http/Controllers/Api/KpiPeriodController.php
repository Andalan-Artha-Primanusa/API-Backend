<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\KpiPeriod;
use App\Models\KpiItem;
use App\Helpers\ApiResponse;
use App\Traits\HasEmployee;

class KpiPeriodController extends Controller
{
    use HasEmployee;

    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $query = KpiPeriod::with([
                'employee:id,user_id,position,department',
                'employee.user:id,name,email',
                'items',
            ]);

            if ($user->isManager() && !$user->isAdmin() && !$user->isHR()) {
                $subordinateIds = $user->teamMembers()->pluck('id')->filter()->toArray();
                if (empty($subordinateIds)) {
                    return ApiResponse::success('No KPI periods', collect());
                }
                $query->whereIn('employee_id', $subordinateIds);
            }

            $periods = $query->latest()->get();

            $periods->each(fn($p) => $p->calculateOverallScore());

            return ApiResponse::success('KPI periods retrieved', $periods);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch KPI periods', null, 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'period_type' => 'required|in:quarterly,semi_annual,annual',
                'period_date' => 'required|date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.indicator' => 'required|string|max:255',
                'items.*.description' => 'nullable|string',
                'items.*.category' => 'nullable|string',
                'items.*.measurement_method' => 'nullable|string',
                'items.*.formula_type' => 'nullable|string',
                'items.*.weight' => 'required|integer|min:0|max:100',
                'items.*.target' => 'required|numeric|min:0',
                'items.*.source' => 'nullable|string',
            ]);

            $periodLabel = KpiPeriod::generateLabel($validated['period_type'], $validated['period_date']);
            $dateRange = KpiPeriod::getDateRange($validated['period_type'], $validated['period_date']);

            $period = KpiPeriod::create([
                'employee_id' => $validated['employee_id'],
                'period_type' => $validated['period_type'],
                'period_label' => $periodLabel,
                'start_date' => $dateRange['start'],
                'end_date' => $dateRange['end'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = new KpiItem();
                $item->kpi_period_id = $period->id;
                $item->indicator = $itemData['indicator'];
                $item->description = $itemData['description'] ?? null;
                $item->category = $itemData['category'] ?? null;
                $item->measurement_method = $itemData['measurement_method'] ?? 'direct';
                $item->formula_type = $itemData['formula_type'] ?? 'standard';
                $item->weight = $itemData['weight'];
                $item->target = $itemData['target'];
                $item->achievement = 0;
                $item->score = 0;
                $item->source = $itemData['source'] ?? null;
                $item->save();
            }

            $period->load(['employee.user', 'items']);

            return ApiResponse::success('KPI period created', $period, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create KPI period', null, 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $period = KpiPeriod::with([
                'employee.user',
                'items',
                'creator:id,name',
            ])->findOrFail($id);

            $period->calculateOverallScore();

            return ApiResponse::success('KPI period detail', $period);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'KPI period not found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch KPI period', null, 500);
        }
    }

    public function updateItems(Request $request, int $id): JsonResponse
    {
        try {
            $period = KpiPeriod::findOrFail($id);

            if ($period->status !== 'draft') {
                return ApiResponse::error('Cannot edit non-draft KPI period', null, 400);
            }

            $validated = $request->validate([
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.id' => 'nullable|exists:kpi_items,id',
                'items.*.indicator' => 'required|string|max:255',
                'items.*.description' => 'nullable|string',
                'items.*.category' => 'nullable|string',
                'items.*.measurement_method' => 'nullable|string',
                'items.*.formula_type' => 'nullable|string',
                'items.*.weight' => 'required|integer|min:0|max:100',
                'items.*.target' => 'required|numeric|min:0',
                'items.*.achievement' => 'nullable|numeric|min:0',
                'items.*.source' => 'nullable|string',
            ]);

            if (isset($validated['notes'])) {
                $period->update(['notes' => $validated['notes']]);
            }

            $submittedIds = [];

            foreach ($validated['items'] as $itemData) {
                $data = [
                    'indicator' => $itemData['indicator'],
                    'description' => $itemData['description'] ?? null,
                    'category' => $itemData['category'] ?? null,
                    'measurement_method' => $itemData['measurement_method'] ?? 'direct',
                    'formula_type' => $itemData['formula_type'] ?? 'standard',
                    'weight' => $itemData['weight'],
                    'target' => $itemData['target'],
                    'source' => $itemData['source'] ?? null,
                ];

                if (isset($itemData['achievement'])) {
                    $data['achievement'] = $itemData['achievement'];
                }

                if (!empty($itemData['id'])) {
                    $item = KpiItem::find($itemData['id']);
                    if ($item && $item->kpi_period_id === $period->id) {
                        $item->update($data);
                        if (isset($itemData['achievement'])) {
                            $item->calculateScore();
                            $item->save();
                        }
                        $submittedIds[] = $item->id;
                    }
                } else {
                    $item = $period->items()->create($data);
                    if (isset($itemData['achievement'])) {
                        $item->calculateScore();
                        $item->save();
                    }
                    $submittedIds[] = $item->id;
                }
            }

            $period->items()->whereNotIn('id', $submittedIds)->delete();

            $period->load('items');
            $period->calculateOverallScore();
            $period->save();

            return ApiResponse::success('KPI period updated', $period->fresh(['employee.user', 'items']));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'KPI period not found', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update KPI period', null, 500);
        }
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        try {
            $employee = $this->getAuthenticatedEmployee();
            $period = KpiPeriod::findOrFail($id);

            if ($period->employee_id !== $employee->id) {
                return ApiResponse::error('Forbidden', 'Not your KPI period', 403);
            }
            if ($period->status !== 'draft') {
                return ApiResponse::error('Already submitted', null, 400);
            }

            $period->items()->where('status', 'draft')->update(['status' => 'submitted']);
                    $period = KpiPeriod::with('items')->findOrFail($id);

                    $validated = $request->validate([
                        'item_id' => 'nullable|exists:kpi_items,id',
                    ]);

            return ApiResponse::success('KPI period submitted', $period->fresh(['items']));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', null, 404);

                    if (!empty($validated['item_id'])) {
                        $item = $period->items->firstWhere('id', (int) $validated['item_id']);

                        if (!$item) {
                            return ApiResponse::error('Not found', 'KPI item not found in this period', 404);
                        }

                        if ($item->status !== 'draft') {
                            return ApiResponse::error('Already submitted', null, 400);
                        }

                        $item->status = 'submitted';
                        $item->save();

                        $this->syncPeriodStatus($period);

                        return ApiResponse::success('KPI item submitted', $period->fresh(['employee.user', 'items']));
                    }

                    if ($period->status !== 'draft') {
                        return ApiResponse::error('Already submitted', null, 400);
        }
    }

                    $period->update(['status' => 'submitted']);
    {
        try {
            $period = KpiPeriod::findOrFail($id);

            if ($period->status !== 'submitted') {
                return ApiResponse::error('KPI must be submitted first', null, 400);
            }

            $items = $period->items;

            foreach ($items as $item) {
                $item->calculateScore();
                $item->status = 'approved';
                $item->save();
            }

            $period->calculateOverallScore();
            $period->status = 'approved';
            $period->save();

            return ApiResponse::success('KPI period approved', $period->fresh(['employee.user', 'items']));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', null, 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to approve', null, 500);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $period = KpiPeriod::findOrFail($id);
            $period->delete();

            return ApiResponse::success('KPI period deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', null, 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete', null, 500);
        }
    }

    // Employee self-service
    public function myKpiPeriods(Request $request): JsonResponse
    {
        try {
            $employee = $this->getAuthenticatedEmployee();

            $periods = KpiPeriod::with('items')
                ->where('employee_id', $employee->id)
                ->latest()
                ->get();

                        $period = KpiPeriod::with('items')->findOrFail($id);

                        $validated = $request->validate([
                            'item_id' => 'nullable|exists:kpi_items,id',
                        ]);

                        if (!empty($validated['item_id'])) {
                            $item = $period->items->firstWhere('id', (int) $validated['item_id']);

                            if (!$item) {
                                return ApiResponse::error('Not found', 'KPI item not found in this period', 404);
                            }

                            if ($item->status !== 'submitted') {
                                return ApiResponse::error('KPI item must be submitted first', null, 400);
                            }

                            $item->calculateScore();
                            $item->status = 'approved';
                            $item->save();

                            $this->syncPeriodStatus($period);

                            return ApiResponse::success('KPI item approved', $period->fresh(['employee.user', 'items']));
                        }

            return ApiResponse::success('My KPI periods', $periods);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch', null, 500);
        }
    }

                        if ($items->contains(fn($item) => $item->status === 'draft')) {
                            return ApiResponse::error('All KPI items must be submitted first', null, 400);
                        }

    public function myUpdateItems(Request $request, int $id): JsonResponse
        try {
                            $item->calculateScore();
            $employee = $this->getAuthenticatedEmployee();
            $period = KpiPeriod::with('items')->findOrFail($id);

                        $this->syncPeriodStatus($period);

            if ($period->status !== 'draft') {
                return ApiResponse::error('Cannot edit non-draft KPI period', null, 400);
            }

            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|exists:kpi_items,id',
                'items.*.achievement' => 'nullable|numeric|min:0',
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = $period->items->firstWhere('id', (int) $itemData['id']);

                if (!$item) {
                    return ApiResponse::error('Validation failed', [
                        'items' => ['One or more KPI items do not belong to this period.'],
                    ], 422);
                }

                $item->achievement = $itemData['achievement'] ?? 0;
                $item->calculateScore();
                $item->status = 'draft';
                $item->save();
            }

            $period->calculateOverallScore();
            $period->save();

            return ApiResponse::success('KPI period items updated', $period->fresh(['employee.user', 'items']));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'KPI period not found', 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update KPI period items', null, 500);
        }
    }

    private function syncPeriodStatus(KpiPeriod $period): void
    {
        $period->loadMissing('items');

        if ($period->items->isEmpty()) {
            $period->status = 'draft';
            $period->calculateOverallScore();
            $period->save();
            return;
        }

        $allApproved = $period->items->every(fn($item) => $item->status === 'approved');
        $hasInReview = $period->items->contains(fn($item) => in_array($item->status, ['submitted', 'approved'], true));

        if ($allApproved) {
            $period->status = 'approved';
        } elseif ($hasInReview) {
            $period->status = 'submitted';
        } else {
            $period->status = 'draft';
        }

        $period->calculateOverallScore();
        $period->save();
    }

    public function mySubmit(Request $request, int $id): JsonResponse
    {
        return $this->submit($request, $id);
    }
}
