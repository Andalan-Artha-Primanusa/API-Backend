<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\OvertimeRequest;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    /**
     * GET /my/overtime - Employee's own overtime requests
     */
    public function myOvertimeRequests(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return ApiResponse::error('Employee not found', null, 404);
        }

        $requests = OvertimeRequest::where('employee_id', $employee->id)
            ->with(['attendance', 'approver:id,name'])
            ->latest('date')
            ->get();

        return ApiResponse::success('My overtime requests', $requests);
    }

    /**
     * PUT /my/overtime/{id}/reason - Employee adds reason to overtime request
     */
    public function addReason(Request $request, int $id): JsonResponse
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return ApiResponse::error('Employee not found', null, 404);
        }

        $overtimeRequest = OvertimeRequest::where('employee_id', $employee->id)
            ->where('id', $id)
            ->first();

        if (!$overtimeRequest) {
            return ApiResponse::error('Overtime request not found', null, 404);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $overtimeRequest->update(['reason' => $validated['reason']]);

        return ApiResponse::success('Reason updated', $overtimeRequest->fresh());
    }

    /**
     * GET /overtime/requests - Admin/Manager view all overtime requests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $query = OvertimeRequest::with(['employee.user:id,name,email', 'attendance', 'approver:id,name'])
            ->latest('date');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return ApiResponse::success('Overtime requests', $query->get());
    }

    /**
     * GET /overtime/requests/pending - Admin/Manager view pending requests
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $requests = OvertimeRequest::where('status', 'pending')
            ->with(['employee.user:id,name,email', 'attendance', 'approver:id,name'])
            ->latest('date')
            ->get();

        return ApiResponse::success('Pending overtime requests', $requests);
    }

    /**
     * PUT /overtime/requests/{id}/approve - Approve overtime request
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $overtimeRequest = OvertimeRequest::with('employee.user')->findOrFail($id);

        if ($overtimeRequest->status !== 'pending') {
            return ApiResponse::error('Request already processed', null, 422);
        }

        $overtimeRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Send notification to employee
        if ($overtimeRequest->employee && $overtimeRequest->employee->user) {
            UserNotification::create([
                'user_id' => $overtimeRequest->employee->user->id,
                'title' => 'Lembur Disetujui',
                'message' => 'Pengajuan lembur Anda tanggal ' . $overtimeRequest->date->format('d M Y') . ' (' . floor($overtimeRequest->overtime_minutes / 60) . 'j ' . ($overtimeRequest->overtime_minutes % 60) . 'm) telah disetujui.',
                'type' => 'overtime',
                'is_read' => false,
            ]);
        }

        return ApiResponse::success('Overtime request approved', $overtimeRequest->fresh(['employee.user', 'approver']));
    }

    /**
     * PUT /overtime/requests/{id}/reject - Reject overtime request
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR() || $user->isManager())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'reject_reason' => 'sometimes|string|max:500',
        ]);

        $overtimeRequest = OvertimeRequest::with('employee.user')->findOrFail($id);

        if ($overtimeRequest->status !== 'pending') {
            return ApiResponse::error('Request already processed', null, 422);
        }

        $overtimeRequest->update([
            'status' => 'rejected',
            'reject_reason' => $validated['reject_reason'] ?? null,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Send notification to employee
        if ($overtimeRequest->employee && $overtimeRequest->employee->user) {
            UserNotification::create([
                'user_id' => $overtimeRequest->employee->user->id,
                'title' => 'Lembur Ditolak',
                'message' => 'Pengajuan lembur Anda tanggal ' . $overtimeRequest->date->format('d M Y') . ' telah ditolak.' . ($validated['reject_reason'] ?? ''),
                'type' => 'overtime',
                'is_read' => false,
            ]);
        }

        return ApiResponse::success('Overtime request rejected', $overtimeRequest->fresh(['employee.user', 'approver']));
    }
}
