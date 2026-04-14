<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\OKR;
use App\Models\ReviewCycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OKRController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OKR::query();

        // Filter by employee
        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        // Filter by period
        if ($request->has('period_id')) {
            $query->where('period_id', $request->integer('period_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        $okrs = $query->with(['employee', 'period', 'createdBy', 'approvedBy'])
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return ApiResponse::success('OKRs retrieved successfully', $okrs);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'period_id' => 'nullable|integer|exists:review_cycles,id',
            'objective' => 'required|string|max:500',
            'description' => 'nullable|string',
            'weight' => 'sometimes|integer|min:1|max:100',
            'target_value' => 'nullable|numeric',
            'unit' => 'nullable|string|in:count,percentage,amount,hours,items,yes/no',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'draft';
        $validated['weight'] = $validated['weight'] ?? 100;

        $okr = OKR::create($validated);

        return ApiResponse::success('OKR created successfully', $okr->load(['employee', 'period', 'createdBy']), 201);
    }

    public function show($id): JsonResponse
    {
        $okr = OKR::with(['employee', 'period', 'createdBy', 'approvedBy'])->findOrFail($id);
        return ApiResponse::success('OKR retrieved successfully', $okr);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $okr = OKR::findOrFail($id);

        $validated = $request->validate([
            'objective' => 'sometimes|string|max:500',
            'description' => 'nullable|string',
            'weight' => 'sometimes|integer|min:1|max:100',
            'target_value' => 'nullable|numeric',
            'current_value' => 'nullable|numeric',
            'unit' => 'nullable|string|in:count,percentage,amount,hours,items,yes/no',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Only allow updates if draft or rejected
        if (!in_array($okr->status, ['draft', 'cancelled'])) {
            return ApiResponse::error('Cannot update OKR in ' . $okr->status . ' status', null, 403);
        }

        $okr->update($validated);

        return ApiResponse::success('OKR updated successfully', $okr->fresh(['employee', 'period', 'createdBy']));
    }

    public function submit(Request $request, $id): JsonResponse
    {
        $okr = OKR::findOrFail($id);

        if ($okr->status !== 'draft') {
            return ApiResponse::error('Only draft OKRs can be submitted', null, 422);
        }

        $okr->submit();

        return ApiResponse::success('OKR submitted successfully', $okr->fresh(['employee', 'period', 'createdBy']));
    }

    public function approve(Request $request, $id): JsonResponse
    {
        $okr = OKR::findOrFail($id);

        $validated = $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        if ($okr->status !== 'submitted') {
            return ApiResponse::error('Only submitted OKRs can be approved', null, 422);
        }

        $okr->approve($request->user()->id, $validated['approval_notes'] ?? null);

        return ApiResponse::success('OKR approved successfully', $okr->fresh(['employee', 'period', 'approvedBy']));
    }

    public function updateProgress(Request $request, $id): JsonResponse
    {
        $okr = OKR::findOrFail($id);

        $validated = $request->validate([
            'current_value' => 'required|numeric',
        ]);

        $okr->update($validated);

        return ApiResponse::success('OKR progress updated. Completion: ' . round($okr->getProgressPercentage(), 2) . '%', $okr->fresh());
    }

    public function markInProgress($id): JsonResponse
    {
        $okr = OKR::findOrFail($id);

        if ($okr->status !== 'approved') {
            return ApiResponse::error('Only approved OKRs can start', null, 422);
        }

        $okr->markInProgress();

        return ApiResponse::success('OKR marked as in progress', $okr->fresh());
    }

    public function markCompleted($id): JsonResponse
    {
        $okr = OKR::findOrFail($id);

        if (!in_array($okr->status, ['in_progress', 'approved'])) {
            return ApiResponse::error('Invalid OKR status for completion', null, 422);
        }

        $okr->markCompleted();

        return ApiResponse::success('OKR marked as completed', $okr->fresh());
    }

    public function destroy($id): JsonResponse
    {
        $okr = OKR::findOrFail($id);

        if ($okr->status !== 'draft') {
            return ApiResponse::error('Only draft OKRs can be deleted', null, 422);
        }

        $okr->delete();

        return ApiResponse::success('OKR deleted successfully');
    }
}
