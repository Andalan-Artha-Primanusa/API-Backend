<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\ApproveLeaveRequest;
use App\Models\Leave;
use App\Services\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(
        protected LeaveService $leaveService
    ) {}

    /**
     * Create a new leave request.
     * Authorization handled by StoreLeaveRequest.
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        try {
            $leave = $this->leaveService->createLeave(
                $request->user(),
                $request->validated()
            );

            return ApiResponse::success('Leave request submitted', $leave, 201);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    /**
     * List leaves based on user role/permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasPermission('leave.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $leaves = $this->leaveService->getLeavesByRole($user);

        return ApiResponse::success('Leave list', $leaves);
    }

    /**
     * Approve or reject a leave request (dynamic multi-step flow).
     * Authorization handled by ApproveLeaveRequest.
     */
    public function update(ApproveLeaveRequest $request, $id): JsonResponse
    {
        $leave = Leave::with('flow.steps.role')->findOrFail($id);

        try {
            $result = $this->leaveService->processApproval(
                $leave,
                $request->user(),
                $request->validated()['status']
            );
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 403);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }

        $message = $result['final']
            ? 'Leave ' . $result['action'] . ' (final)'
            : 'Approved step, proceeding to next step';

        return ApiResponse::success($message, $result);
    }

    // 📌 REJECT
    public function reject(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_note' => $request->note
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuti ditolak'
        ]);
    }

    // 📌 CALENDAR
    public function calendar()
    {
        $employeeId = $this->getEmployeeId();

        $leaves = Leave::where('employee_id', $employeeId)->get();

        return $leaves->map(function ($leave) {
            return [
                'title' => strtoupper($leave->type),
                'start' => $leave->start_date,
                'end'   => $leave->end_date,
                'status'=> $leave->status
            ];
        });
    }
}