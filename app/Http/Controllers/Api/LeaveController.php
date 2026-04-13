<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\ApproveLeaveRequest;
use App\Models\Leave;
use App\Models\LeavePolicy;
use App\Services\LeaveService;
use App\Traits\HasEmployee;
use App\Enums\LeaveStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    use HasEmployee;

    public function __construct(
        protected LeaveService $leaveService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | EMPLOYEE SELF-SERVICE (ESS)
    |--------------------------------------------------------------------------
    */

    public function myLeaves(Request $request): JsonResponse
    {
        // Using user_id here as Leave is tied contextually to user mapping in current DB
        $employee = $this->getAuthenticatedEmployee();

        $leaves = Leave::where('employee_id', $employee->id)
            ->with(['user.profile', 'employee.user.profile', 'approver.profile'])
            ->latest()
            ->get();

        return ApiResponse::success('My Leaves', $leaves);
    }

    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $balance = $this->leaveService->getLeaveBalance($user);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }

        return ApiResponse::success('Leave balance', $balance);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERAL / ADMIN ROUTES
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isHR() && !$user->hasPermission('leave.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $leaves = $this->leaveService->getLeavesByRole($user);

        return ApiResponse::success('Leave list', $leaves);
    }

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

    public function show(Request $request, $id): JsonResponse
    {
        $leave = Leave::with(['user.profile', 'employee.user.profile', 'approver.profile', 'flow.steps.role'])->findOrFail($id);

        $user = $request->user();

        if ($leave->user_id !== $user->id && !$user->isAdmin() && !$user->isHR() && !$user->isManager()) {
            return ApiResponse::error('Forbidden', 'No permission to view this leave', 403);
        }

        return ApiResponse::success('Leave details', $leave);
    }

    public function calendar(Request $request): JsonResponse
    {
        // Viewable based on filters
        $query = Leave::with('employee.user.profile');

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $leaves = $query->get();

        $data = $leaves->map(function ($leave) {
            return [
                'id' => $leave->id,
                'title' => strtoupper($leave->type) . ' - ' . ($leave->employee?->user?->name ?? 'User'),
                'start' => $leave->start_date,
                'end'   => $leave->end_date,
                'status' => $leave->status
            ];
        });

        return ApiResponse::success('Leave calendar', $data);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $leave = Leave::findOrFail($id);

        if (!$leave->isPending()) {
            return ApiResponse::error('Only pending leaves can be updated', null, 400);
        }

        // Ensure user owns the leave or is admin
        $user = $request->user();

        if ($leave->user_id !== $user->id && !$user->isAdmin()) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        // Just handle basic updates (e.g. reason) for end user
        $request->validate([
            'reason' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date'
        ]);

        try {
            $leave = $this->leaveService->updatePendingLeave($leave, $request->only(['reason', 'start_date', 'end_date']));
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }

        return ApiResponse::success('Leave updated successfully', $leave);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $leave = Leave::findOrFail($id);

        if (!$leave->isPending()) {
            return ApiResponse::error('Only pending leaves can be deleted', null, 400);
        }

        if ($leave->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $deleted = $leave->load(['user.profile', 'employee.user.profile', 'approver.profile', 'flow.steps.role'])->toArray();

        try {
            $this->leaveService->deletePendingLeave($leave);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }

        return ApiResponse::success('Leave deleted successfully', $deleted);
    }

    /*
    |--------------------------------------------------------------------------
    | HR / MANAGER APPROVAL FLOW
    |--------------------------------------------------------------------------
    */

    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Leave::with(['user.profile', 'employee.user.profile'])
            ->where('status', LeaveStatus::Pending);

        // Scope by role: managers see only subordinates' pending leaves
        if ($user->isManager() && !$user->isAdmin() && !$user->isHR()) {
            $subordinateUserIds = $user->teamMembers()->pluck('user_id');
            $query->whereIn('user_id', $subordinateUserIds);
        } elseif (!$user->isAdmin() && !$user->isHR()) {
            $query->where('user_id', $user->id);
        }

        $leaves = $query->latest()->get();

        return ApiResponse::success('Pending leaves', $leaves);
    }

    public function approve(ApproveLeaveRequest $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isManager() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }
        $leave = Leave::with('flow.steps.role')->findOrFail($id);

        try {
            $result = $this->leaveService->processApproval(
                $leave,
                $request->user(),
                'approved'
            );
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 403);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }

        $message = $result['final']
            ? 'Leave completely approved'
            : 'Approved step, proceeding to next step';

        return ApiResponse::success($message, $result);
    }

    public function reject(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isManager() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }
        $leave = Leave::findOrFail($id);

        $request->validate([
            'note' => 'required|string|max:500'
        ]);

        if (!$leave->isPending()) {
            return ApiResponse::error('Leave is not pending', null, 400);
        }

        $leave->reject($request->user()->id, $request->note);

        return ApiResponse::success('Leave rejected successfully', $leave->fresh(['user.profile', 'employee.user.profile', 'approver.profile', 'flow.steps.role']));
    }
}
