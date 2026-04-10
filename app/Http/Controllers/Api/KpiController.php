<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Models\Kpi;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreKpiRequest;
use App\Traits\HasEmployee;

class KpiController extends Controller
{
    use HasEmployee;

    /**
     * Default relations to eager load for KPI queries
     */
    private const KPI_RELATIONS = [
        'employee:id,user_id,position,department,manager_id',
        'employee.user:id,name,email',
        'employee.user.profile:id,user_id,phone,address',
        'employee.manager:id,user_id,position',
        'employee.manager.profile:id,user_id,phone',
    ];

    /*
    |--------------------------------------------------------------------------
    | 🔥 HR / MANAGER (Admin Level)
    |--------------------------------------------------------------------------
    */

    /**
     * Get all KPIs with filtering by manager scope.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Optimized query with eager loading
            $query = Kpi::with(self::KPI_RELATIONS)
                ->select(['id', 'employee_id', 'title', 'description', 'target', 'achievement', 'score', 'status', 'created_at', 'updated_at'])
                ->latest();

            // Scope by manager subordinates if not admin/hr
            if ($user->isManager() && !$user->isAdmin() && !$user->isHR()) {
                $subordinateIds = $user->teamMembers()
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                if (empty($subordinateIds)) {
                    return ApiResponse::success('No KPIs available', collect());
                }

                $query->whereIn('employee_id', $subordinateIds);
            }

            $kpis = $query->get();

            return ApiResponse::success('KPIs retrieved successfully', $kpis);

        } catch (\Exception $e) {
            \Log::error('KPI Index Error', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch KPIs', null, 500);
        }
    }

    /**
     * Create new KPI record.
     */
    public function store(StoreKpiRequest $request): JsonResponse
    {
        try {
            $kpi = Kpi::create(array_merge(
                $request->validated(),
                [
                    'achievement' => 0,
                    'score'       => 0,
                    'status'      => 'draft',
                ]
            ));

            return ApiResponse::success(
                'KPI created successfully',
                $kpi->load(self::KPI_RELATIONS),
                201
            );

        } catch (\Exception $e) {
            \Log::error('KPI Store Error', ['error' => $e->getMessage()]);
            return ApiResponse::error('Failed to create KPI', null, 500);
        }
    }

    /**
     * Show specific KPI detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid KPI ID']);
            }

            $kpi = Kpi::with(self::KPI_RELATIONS)
                ->select(['id', 'employee_id', 'title', 'description', 'target', 'achievement', 'score', 'status', 'created_at', 'updated_at'])
                ->findOrFail($id);

            return ApiResponse::success('KPI details', $kpi);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'KPI record not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('KPI Show Error', ['id' => $id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch KPI', null, 500);
        }
    }

    /**
     * Update KPI record with auto-score calculation.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid KPI ID']);
            }

            $kpi = Kpi::findOrFail($id);

            $validated = $request->validate([
                'title'       => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'target'      => 'sometimes|numeric|min:0',
                'achievement' => 'sometimes|numeric|min:0',
            ]);

            $kpi->update($validated);

            // Auto-calculate score if target is set
            if ($kpi->target > 0) {
                $kpi->score = ($kpi->achievement / $kpi->target) * 100;
                $kpi->save();
            }

            return ApiResponse::success(
                'KPI updated successfully',
                $kpi->fresh(self::KPI_RELATIONS)
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'KPI record not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('KPI Update Error', ['id' => $id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to update KPI', null, 500);
        }
    }

    /**
     * Delete KPI record.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid KPI ID']);
            }

            $kpi = Kpi::select(['id', 'employee_id', 'title', 'target', 'achievement', 'status'])->findOrFail($id);
            $deleted = $kpi->toArray();
            $kpi->delete();

            \Log::info('KPI Deleted', ['deleted_id' => $id, 'deleted_by' => $request->user()->id]);

            return ApiResponse::success('KPI deleted successfully', $deleted);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'KPI record not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('KPI Delete Error', ['id' => $id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to delete KPI', null, 500);
        }
    }

    /**
     * Get all KPIs for specific employee.
     */
    public function byEmployee(Request $request, int $employee_id): JsonResponse
    {
        try {
            if ($employee_id <= 0) {
                throw ValidationException::withMessages(['employee_id' => 'Invalid employee ID']);
            }

            $kpis = Kpi::with(self::KPI_RELATIONS)
                ->where('employee_id', $employee_id)
                ->select(['id', 'employee_id', 'title', 'description', 'target', 'achievement', 'score', 'status', 'created_at', 'updated_at'])
                ->latest()
                ->get();

            return ApiResponse::success('Employee KPIs', $kpis);

        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('KPI By Employee Error', ['employee_id' => $employee_id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch KPIs', null, 500);
        }
    }

    /**
     * Approve KPI record.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid KPI ID']);
            }

            $kpi = Kpi::findOrFail($id);

            if ($kpi->status !== 'draft' && $kpi->status !== 'submitted') {
                return ApiResponse::error('Invalid status', 'KPI cannot be approved in current status', 400);
            }

            $kpi->update(['status' => 'approved']);

            \Log::info('KPI Approved', ['kpi_id' => $id, 'approved_by' => $request->user()->id]);

            return ApiResponse::success('KPI approved successfully', $kpi->fresh(self::KPI_RELATIONS));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'KPI record not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('KPI Approve Error', ['id' => $id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to approve KPI', null, 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 EMPLOYEE SELF-SERVICE (ESS)
    |--------------------------------------------------------------------------
    */

    /**
     * Get authenticated employee's KPIs.
     */
    public function myKpi(Request $request): JsonResponse
    {
        try {
            $employee = $this->getAuthenticatedEmployee();

            $kpis = Kpi::with(self::KPI_RELATIONS)
                ->where('employee_id', $employee->id)
                ->select(['id', 'employee_id', 'title', 'description', 'target', 'achievement', 'score', 'status', 'created_at', 'updated_at'])
                ->latest()
                ->get();

            return ApiResponse::success('My KPIs', $kpis);

        } catch (\Exception $e) {
            \Log::error('My KPI Error', ['user_id' => $request->user()->id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to fetch your KPIs', null, 500);
        }
    }

    /**
     * Submit KPI for approval by employee.
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid KPI ID']);
            }

            $employee = $this->getAuthenticatedEmployee();
            $kpi = Kpi::findOrFail($id);

            // Authorization: must be own KPI
            if ($kpi->employee_id !== $employee->id) {
                return ApiResponse::error('Forbidden', 'You cannot submit this KPI', 403);
            }

            // Validation: must be in draft status
            if ($kpi->status !== 'draft') {
                return ApiResponse::error('Invalid status', 'KPI is already submitted or processed', 400);
            }

            $kpi->update(['status' => 'submitted']);

            \Log::info('KPI Submitted', ['kpi_id' => $id, 'submitted_by' => $request->user()->id]);

            return ApiResponse::success('KPI submitted successfully', $kpi->fresh(self::KPI_RELATIONS));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'KPI record not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            \Log::error('KPI Submit Error', ['id' => $id, 'error' => $e->getMessage()]);
            return ApiResponse::error('Failed to submit KPI', null, 500);
        }
    }
}