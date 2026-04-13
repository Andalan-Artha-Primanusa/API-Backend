<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportingController extends Controller
{
    public function __construct(
        protected ReportingService $reportingService
    ) {}

    /**
     * Get comprehensive HR dashboard summary
     */
    public function dashboardSummary(Request $request): JsonResponse
    {
        $this->authorizeReporting($request);

        try {
            $filters = $request->only(['department', 'month', 'year', 'start_date', 'end_date']);
            $data = $this->reportingService->getDashboardSummary($filters);

            return ApiResponse::success('Dashboard summary retrieved successfully', $data);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve dashboard summary', $e->getMessage(), 500);
        }
    }

    /**
     * Get detailed attendance analytics
     */
    public function attendanceAnalytics(Request $request): JsonResponse
    {
        $this->authorizeReporting($request);

        try {
            $filters = $request->only(['start_date', 'end_date']);
            $data = $this->reportingService->getAttendanceAnalytics($filters);

            return ApiResponse::success('Attendance analytics retrieved successfully', $data);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve attendance analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Get leave utilization analytics
     */
    public function leaveAnalytics(Request $request): JsonResponse
    {
        $this->authorizeReporting($request);

        try {
            $filters = $request->only(['year']);
            $data = $this->reportingService->getLeaveAnalytics($filters);

            return ApiResponse::success('Leave analytics retrieved successfully', $data);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve leave analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Get payroll analytics
     */
    public function payrollAnalytics(Request $request): JsonResponse
    {
        $this->authorizeReporting($request);

        try {
            $filters = $request->only(['start_date', 'end_date', 'month', 'year']);
            $data = $this->reportingService->getPayrollAnalytics($filters);

            return ApiResponse::success('Payroll analytics retrieved successfully', $data);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve payroll analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Get competency and training analytics
     */
    public function competencyAnalytics(Request $request): JsonResponse
    {
        $this->authorizeReporting($request);

        try {
            $data = $this->reportingService->getCompetencyAnalytics();

            return ApiResponse::success('Competency analytics retrieved successfully', $data);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve competency analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Get employee lifecycle analytics
     */
    public function employeeLifecycleAnalytics(Request $request): JsonResponse
    {
        $this->authorizeReporting($request);

        try {
            $filters = $request->only(['year']);
            $data = $this->reportingService->getEmployeeLifecycleAnalytics($filters);

            return ApiResponse::success('Employee lifecycle analytics retrieved successfully', $data);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve employee lifecycle analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Get asset management analytics
     */
    public function assetAnalytics(Request $request): JsonResponse
    {
        $this->authorizeReporting($request);

        try {
            $data = $this->reportingService->getAssetAnalytics();

            return ApiResponse::success('Asset analytics retrieved successfully', $data);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve asset analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Authorize user for reporting access
     */
    private function authorizeReporting(Request $request): void
    {
        $user = $request->user();

        // Only HR, Admin, and Super Admin can access reports
        if (!($user->isHR() || $user->isAdmin() || $user->hasRole('super_admin'))) {
            abort(403, 'Unauthorized to access reporting');
        }
    }
}
