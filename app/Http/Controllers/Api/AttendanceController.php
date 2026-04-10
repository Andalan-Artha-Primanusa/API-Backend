<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\CheckInRequest;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Check-in with geofencing validation.
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {

        if (!$request->user()->hasPermission('attendance.check_in')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        try {
            $result = $this->attendanceService->checkIn(
                $request->user(),
                $request->validated()['latitude'],
                $request->validated()['longitude']
            );

            return ApiResponse::success('Check-in successful', [
                'location' => $result['location'],
                'distance' => $result['distance'] . ' meter',
                'data'     => $result['attendance'],
                'status' => $result['attendance']->status,
            ]);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Check-out for today's attendance.
     */
    public function checkOut(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('attendance.check_out')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        try {
            $attendance = $this->attendanceService->checkOut($request->user());

            return ApiResponse::success('Check-out successful', $attendance);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Get attendance history for the current user.
     */
    public function history(Request $request): JsonResponse
    {

        if (!$request->user()->hasPermission('attendance.view_own')) {
        return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $data = $this->attendanceService->getHistory($request->user());

        return ApiResponse::success('Attendance history', $data);
    }

    /**
     * Get today's attendance for the current user.
     */
    public function today(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('attendance.view_own')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $attendance = $this->attendanceService->getToday($request->user());

        return ApiResponse::success('Today attendance', $attendance);
    }

    /**
     * Get all attendance records (admin).
     */
    public function all(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('attendance.view_all')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $data = $this->attendanceService->getAll();

        return ApiResponse::success('All attendance records', $data);
    }

    /**
     * Show a specific attendance record.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $attendance = \App\Models\Attendance::with('user.profile')->findOrFail($id);
        $user = $request->user();

        // Non-owner must have attendance.view_all permission
        if ($attendance->user_id !== $user->id && !$user->hasPermission('attendance.view_all')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        return ApiResponse::success('Attendance detail', $attendance);
    }

    /**
     * Delete an attendance record (admin only).
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasPermission('attendance.delete')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $attendance = \App\Models\Attendance::with('user.profile')->findOrFail($id);
        $deleted = $attendance->toArray();

        $attendance->delete();

        return ApiResponse::success('Attendance record deleted', $deleted);
    }
}
