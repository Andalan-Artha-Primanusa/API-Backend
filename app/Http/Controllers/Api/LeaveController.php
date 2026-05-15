<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Requests\ApproveLeaveRequest;
use App\Models\Leave;
use App\Models\LeavePolicy;
use App\Models\ApprovalFlow;
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
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            if ($user->isAdmin() || $user->isHR() || $user->isManager() || $user->hasPermission('leave.view')) {
                $emptyLeaves = Leave::whereRaw('1 = 0')
                    ->paginate($request->integer('per_page', 10))
                    ->withQueryString();

                return ApiResponse::success('My Leaves', $emptyLeaves);
            }

            return ApiResponse::error('Forbidden: User is not an employee', null, 403);
        }

        $leaves = Leave::where('employee_id', $employee->id)
            ->with([
                'user:id,name,email',
                'user.profile:id,user_id,profile_photo_path',
                'employee:id,user_id,employee_code,department_id,position_id',
                'employee.user:id,name,email',
                'employee.user.profile:id,user_id,profile_photo_path',
                'employee.department:id,name',
                'employee.position:id,name',
                'leaveType:id,name'
            ])
            ->latest()
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return ApiResponse::success('My Leaves', $leaves);
    }

    public function balance(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $balance = $this->leaveService->getLeaveBalance($user);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
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

        if (!$user->isAdmin() && !$user->isHR() && !$user->isManager() && !$user->hasPermission('leave.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $leaves = $this->leaveService->getLeavesByRole($user)->withQueryString();
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
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        $leave = Leave::with([
                'user:id,name,email',
                'user.profile:id,user_id,profile_photo_path',
                'employee:id,user_id,employee_code,department_id,position_id',
                'employee.user:id,name,email',
                'employee.user.profile:id,user_id,profile_photo_path',
                'employee.department:id,name',
                'employee.position:id,name',
                'leaveType:id,name',
                'flow.steps.role:id,name',
                'approver:id,name,email',
                'approver.profile:id,user_id,profile_photo_path',
                'approver.employee:id,user_id,position_id',
                'approver.employee.position:id,name'
            ])
            ->findOrFail($id);

        $user = $request->user();

        if ($leave->user_id !== $user->id && !$user->isAdmin() && !$user->isHR() && !$user->isManager() && !$user->hasPermission('leave.view')) {
            return ApiResponse::error('Forbidden', 'No permission to view this leave', 403);
        }

        return ApiResponse::success('Leave details', $leave);
    }

    public function calendar(Request $request): JsonResponse
    {
        // Viewable based on filters
        $query = Leave::with([
            'employee:id,user_id,employee_code,department_id,position_id',
            'employee.user:id,name,email',
            'employee.user.profile:id,user_id,profile_photo_path',
            'employee.department:id,name',
            'employee.position:id,name'
        ]);

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $leaves = $query->get();

        $data = $leaves->map(function ($leave) {
            $name = $leave->employee?->user?->name ?? 'User';
            $dept = $leave->employee?->department?->name ?? 'N/A';
            $pos = $leave->employee?->position?->name ?? 'N/A';
            
            return [
                'id' => $leave->id,
                'title' => strtoupper($leave->type) . " - $name ($dept - $pos)",
                'start' => $leave->start_date,
                'end'   => $leave->end_date,
                'status' => $leave->status,
                'employee' => [
                    'id' => $leave->employee_id,
                    'name' => $name,
                    'department' => $dept,
                    'position' => $pos,
                    'avatar' => $leave->employee?->user?->profile?->avatar
                ]
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

        if ($leave->user_id !== $user->id && !$user->isAdmin() && !$user->hasPermission('leave.update')) {
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

        return ApiResponse::success('Leave updated successfully', $leave->fresh([
            'user:id,name,email',
            'user.profile:id,user_id,profile_photo_path',
            'employee:id,user_id,employee_code,department_id,position_id',
            'employee.user:id,name,email',
            'employee.user.profile:id,user_id,profile_photo_path',
            'employee.department:id,name',
            'employee.position:id,name',
            'leaveType:id,name'
        ]));
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $leave = Leave::findOrFail($id);

        if (!$leave->isPending()) {
            return ApiResponse::error('Only pending leaves can be deleted', null, 400);
        }

        if ($leave->user_id !== $request->user()->id && !$request->user()->isAdmin() && !$request->user()->hasPermission('leave.delete')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $deleted = $leave->load([
            'user:id,name,email',
            'user.profile:id,user_id,profile_photo_path',
            'employee:id,user_id,employee_code,department_id,position_id',
            'employee.user:id,name,email',
            'employee.user.profile:id,user_id,profile_photo_path',
            'employee.department:id,name',
            'employee.position:id,name',
            'approver.profile:id,user_id,profile_photo_path',
            'flow.steps.role:id,name'
        ])->toArray();

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

        $query = Leave::with([
                'user:id,name,email',
                'user.profile:id,user_id,profile_photo_path',
                'employee:id,user_id,employee_code,department_id,position_id',
                'employee.user:id,name,email',
                'employee.user.profile:id,user_id,profile_photo_path',
                'employee.department:id,name',
                'employee.position:id,name',
                'leaveType:id,name',
                'flow.steps.role:id,name',
                'approver:id,name,email',
                'approver.profile:id,user_id,profile_photo_path',
                'approver.employee:id,user_id,position_id',
                'approver.employee.position:id,name'
            ])
            ->where('status', LeaveStatus::Pending->value);

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isHR() || $user->hasPermission('leave.approve')) {
            $leaves = $query->latest()->paginate($request->integer('per_page', 10))->withQueryString();
            
            $leaves->getCollection()->transform(function ($leave) {
                $leave->setAttribute('can_act', true);
                return $leave;
            });

            return ApiResponse::success('Pending leaves', $leaves);
        }

        // Cari flow aktif untuk modul leave
        $flow = ApprovalFlow::where('module', 'leave')
            ->where('is_active', true)
            ->with('steps.role')
            ->first();

        if (!$flow || !$flow->steps) {
            return ApiResponse::success('Pending leaves', []);
        }

        // Filter steps di mana user saat ini berhak melakukan approval
        $validSteps = $flow->steps->filter(function ($step) use ($user) {
            if (!$step->role) {
                return false;
            }
            $hasRole = $user->hasRole($step->role->name);
            $userMatches = is_null($step->user_id) || $step->user_id === $user->id;
            return $hasRole && $userMatches;
        });

        if ($validSteps->isEmpty()) {
            return ApiResponse::success('Pending leaves', []);
        }

        // Terapkan filter visibilitas berdasarkan current_step dan batasan subordinate jika manager
        $query->where('approval_flow_id', $flow->id)
            ->where(function ($q) use ($validSteps, $user) {
                $subordinateUserIds = $user->teamMembers()->pluck('user_id');

                foreach ($validSteps as $step) {
                    $q->orWhere(function ($sq) use ($step, $subordinateUserIds) {
                        $sq->where('current_step', $step->step_order);
                        // Jika role yang ditugaskan untuk step ini adalah manager, batasi ke subordinate
                        if ($step->role->name === 'manager') {
                            $sq->whereIn('user_id', $subordinateUserIds);
                        }
                    });
                }
            });

        $leaves = $query->latest()->paginate($request->integer('per_page', 10))->withQueryString();

        // For non-admin users, leaves are already filtered to only show their steps
        $leaves->getCollection()->transform(function ($leave) {
            $leave->setAttribute('can_act', true);
            return $leave;
        });

        return ApiResponse::success('Pending leaves', $leaves);
    }

    public function approve(ApproveLeaveRequest $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isManager() && !$user->isHR() && !$user->hasPermission('leave.approve')) {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }
        $leave = Leave::with('flow.steps.role')->findOrFail($id);

        try {
            $result = $this->leaveService->processApproval(
                $leave,
                $request->user(),
                'approved',
                $request->note
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

        if (!$user->isAdmin() && !$user->isManager() && !$user->isHR() && !$user->hasPermission('leave.approve')) {
            return ApiResponse::error('Forbidden', 'You are not authorized', 403);
        }

        $request->validate([
            'note' => 'required|string|max:500'
        ]);

        $leave = Leave::with('flow.steps.role')->findOrFail($id);

        if (!$leave->isPending()) {
            return ApiResponse::error('Leave is not pending', null, 400);
        }

        try {
            $result = $this->leaveService->processApproval($leave, $request->user(), 'rejected', $request->note);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 403);
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }

        return ApiResponse::success('Leave rejected successfully', $result['leave']->fresh([
            'user:id,name,email',
            'user.profile:id,user_id,profile_photo_path',
            'employee:id,user_id,employee_code,department_id,position_id',
            'employee.user:id,name,email',
            'employee.user.profile:id,user_id,profile_photo_path',
            'employee.department:id,name',
            'employee.position:id,name',
            'leaveType:id,name',
            'flow.steps.role:id,name',
            'approver:id,name,email',
            'approver.profile:id,user_id,profile_photo_path',
            'approver.employee:id,user_id,position_id',
            'approver.employee.position:id,name'
        ]));
    }
}
