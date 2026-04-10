<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kpi;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreKpiRequest;
use App\Traits\HasEmployee;
use Illuminate\Http\JsonResponse;

class KpiController extends Controller
{
    use HasEmployee;

    /*
    |--------------------------------------------------------------------------
    | 🔥 HR / MANAGER (Admin Level)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Kpi::with(['employee.user.profile', 'employee.manager.profile'])->latest();

        if ($user->isManager() && !$user->isAdmin() && !$user->isHR()) {
            $subordinateIds = $user->teamMembers()
                ->pluck('id')
                ->filter();

            $query->whereIn('employee_id', $subordinateIds);
        }

        $kpis = $query->get();

        return ApiResponse::success('KPIs retrieved successfully', $kpis);
    }

    public function store(StoreKpiRequest $request): JsonResponse
    {
        $kpi = Kpi::create(array_merge(
            $request->validated(),
            [
                'achievement' => 0,
                'score' => 0,
                'status' => 'draft',
            ]
        ));

        return ApiResponse::success('KPI created successfully', $kpi->load(['employee.user.profile', 'employee.manager.profile']));
    }

    public function show($id): JsonResponse
    {
        $kpi = Kpi::with(['employee.user.profile', 'employee.manager.profile'])->findOrFail($id);

        return ApiResponse::success('KPI details', $kpi);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $kpi = Kpi::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'target' => 'sometimes|numeric',
            'achievement' => 'sometimes|numeric'
        ]);

        $kpi->update($request->only([
            'title',
            'description',
            'target',
            'achievement'
        ]));

        // 🔥 AUTO HITUNG SCORE
        if ($kpi->target > 0) {
            $kpi->score = ($kpi->achievement / $kpi->target) * 100;
            $kpi->save();
        }

        return ApiResponse::success('KPI updated successfully', $kpi->fresh(['employee.user.profile', 'employee.manager.profile']));
    }

    public function destroy($id): JsonResponse
    {
        $kpi = Kpi::with(['employee.user.profile', 'employee.manager.profile'])->findOrFail($id);
        $deleted = $kpi->toArray();
        $kpi->delete();

        return ApiResponse::success('KPI deleted successfully', $deleted);
    }

    public function byEmployee($employee_id): JsonResponse
    {
        $kpis = Kpi::with(['employee.user.profile', 'employee.manager.profile'])
            ->where('employee_id', $employee_id)
            ->get();

        return ApiResponse::success('Employee KPIs', $kpis);
    }

    public function approve($id): JsonResponse
    {
        $kpi = Kpi::findOrFail($id);

        $kpi->update([
            'status' => 'approved'
        ]);

        return ApiResponse::success('KPI approved successfully', $kpi->fresh(['employee.user.profile', 'employee.manager.profile']));
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 EMPLOYEE SELF-SERVICE (ESS)
    |--------------------------------------------------------------------------
    */

    public function myKpi(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $kpis = Kpi::with(['employee.user.profile', 'employee.manager.profile'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return ApiResponse::success('My KPIs', $kpis);
    }

    public function submit($id): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();
        $kpi = Kpi::findOrFail($id);

        if ($kpi->employee_id !== $employee->id) {
            return ApiResponse::error('Access denied to this KPI', null, 403);
        }

        if ($kpi->status !== 'draft') {
            return ApiResponse::error('KPI is already submitted or processed', null, 400);
        }

        $kpi->update([
            'status' => 'submitted'
        ]);

        return ApiResponse::success('KPI submitted successfully', $kpi->fresh(['employee.user.profile', 'employee.manager.profile']));
    }
}