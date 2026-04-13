<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\ApprovalFlow;
use App\Models\User;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeavePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Enums\LeaveStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveService
{
    /**
     * Create a new leave request and attach it to the approval flow.
     *
     * @throws \RuntimeException if no approval flow is configured for 'leave'
     */
    public function createLeave(User $user, array $data): Leave
    {
        $flow = ApprovalFlow::where('module', 'leave')->first();

        if (!$flow) {
            throw new \RuntimeException('Leave approval flow has not been configured.');
        }

        $employee = $user->employee;
        if (!$employee) {
            throw new \RuntimeException('Employee record not found for this user.');
        }

        $totalDays = Leave::calculateDays($data['start_date'], $data['end_date']);
        $leaveType = $data['type'];

        return DB::transaction(function () use ($user, $data, $flow, $employee, $totalDays, $leaveType) {
            if ($leaveType === Leave::TYPE_ANNUAL) {
                $balance = $this->getOrCreateAnnualBalance($employee->id, (int) date('Y', strtotime($data['start_date'])));

                if ($balance->availableDays() < $totalDays) {
                    throw new \RuntimeException('Insufficient annual leave balance.');
                }

                $balance->increment('pending_days', $totalDays);
            }

            return Leave::create([
                'user_id'          => $user->id,
                'employee_id'      => $employee->id,
                'start_date'       => $data['start_date'],
                'end_date'         => $data['end_date'],
                'total_days'       => $totalDays,
                'type'             => $leaveType,
                'reason'           => $data['reason'] ?? null,
                'status'           => LeaveStatus::Pending,
                'approval_flow_id' => $flow->id,
                'current_step'     => 1,
            ])->load(['user.profile', 'employee.user.profile', 'approver.profile', 'flow.steps.role']);
        });
    }

    /**
     * Retrieve leaves based on the user's role/permissions.
     *
     * Authorization priority (most privileged first):
     * 1. Admin/HR/SuperAdmin → all leaves
     * 2. Manager → subordinates' leaves + own
     * 3. Employee → own leaves only
     */
    public function getLeavesByRole(User $user): LengthAwarePaginator
    {
        $query = Leave::with(['user.profile', 'employee.user.profile', 'approver.profile', 'flow.steps.role']);

        // Admin/HR/SuperAdmin — see all (no filter)
        if ($user->isAdmin() || $user->isHR()) {
            // no filter — all records
        } elseif ($user->isManager()) {
            // Manager — subordinates' leaves + own
            $subordinateUserIds = $user->teamMembers()->pluck('user_id');
            $query->where(function ($q) use ($user, $subordinateUserIds) {
                $q->whereIn('user_id', $subordinateUserIds)
                  ->orWhere('user_id', $user->id);
            });
        } else {
            // Employee (default) — own leaves only
            $query->where('user_id', $user->id);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Process an approval or rejection for a leave request.
     *
     * @throws \DomainException for business rule violations
     * @throws \RuntimeException for system configuration issues
     * @return array{leave: Leave, final: bool, action: string, current_step?: int, next_role?: string}
     */
    public function processApproval(Leave $leave, User $approver, string $action): array
    {
        if (!$leave->isPending()) {
            throw new \DomainException('Leave request has already been processed.');
        }

        if (!$leave->flow) {
            throw new \RuntimeException('Approval flow not found.');
        }

        $step = $leave->flow->steps
            ->where('step_order', $leave->current_step)
            ->first();

        if (!$step) {
            throw new \RuntimeException('Approval step not found.');
        }

        if (!$approver->hasRole($step->role->name)) {
            throw new \DomainException('It is not your turn to approve this request.');
        }

        // Rejection — immediately finalize
        if ($action === 'rejected') {
            $this->releaseAnnualLeave($leave);
            $leave->update(['status' => LeaveStatus::Rejected]);

            return [
                'leave'  => $leave->fresh(),
                'final'  => true,
                'action' => 'rejected',
            ];
        }

        // Check if there's a next step
        $nextStep = $leave->flow->steps
            ->where('step_order', $leave->current_step + 1)
            ->first();

        if ($nextStep) {
            $leave->update(['current_step' => $leave->current_step + 1]);
            $leave->refresh(); // Fix: refresh to get the updated current_step

            return [
                'leave'        => $leave->load(['user.profile', 'employee.user.profile', 'approver.profile', 'flow.steps.role']),
                'final'        => false,
                'action'       => 'approved',
                'current_step' => $leave->current_step,
                'next_role'    => $nextStep->role->name,
            ];
        }

        // Final approval
        $leave->update(['status' => LeaveStatus::Approved]);
        $this->finalizeAnnualLeave($leave);

        return [
            'leave'  => $leave->fresh(['user.profile', 'employee.user.profile', 'approver.profile', 'flow.steps.role']),
            'final'  => true,
            'action' => 'approved',
        ];
    }

    public function getLeaveBalance(User $user): array
    {
        $employee = $user->employee;

        if (!$employee) {
            throw new \RuntimeException('Employee record not found for this user.');
        }

        $year = (int) date('Y');
        $policy = LeavePolicy::where('year', $year)->first() ?? LeavePolicy::firstOrCreate(
            ['year' => $year],
            ['annual_allowance' => 12, 'carry_over_allowance' => 0, 'max_pending_days' => 30, 'active' => true]
        );

        $balance = $this->getOrCreateAnnualBalance($employee->id, $year, $policy);

        return [
            'policy' => $policy,
            'balance' => [
                'allocated_days' => $balance->allocated_days,
                'carry_over_days' => $balance->carry_over_days,
                'used_days' => $balance->used_days,
                'pending_days' => $balance->pending_days,
                'available_days' => $balance->availableDays(),
            ],
        ];
    }

    public function updatePendingLeave(Leave $leave, array $data): Leave
    {
        if (!$leave->isPending()) {
            throw new \DomainException('Only pending leaves can be updated.');
        }

        $oldDays = (int) $leave->total_days;
        $newStart = $data['start_date'] ?? Carbon::parse($leave->start_date)->toDateString();
        $newEnd = $data['end_date'] ?? Carbon::parse($leave->end_date)->toDateString();
        $newDays = Leave::calculateDays($newStart, $newEnd);

        return DB::transaction(function () use ($leave, $data, $oldDays, $newStart, $newEnd, $newDays) {
            if ($leave->type === Leave::TYPE_ANNUAL) {
                $balance = $this->getOrCreateAnnualBalance($leave->employee_id, (int) date('Y', strtotime($newStart)));
                $balance->decrement('pending_days', $oldDays);
                if ($balance->availableDays() < $newDays) {
                    throw new \RuntimeException('Insufficient annual leave balance.');
                }
                $balance->increment('pending_days', $newDays);
            }

            $leave->update([
                'start_date' => $newStart,
                'end_date' => $newEnd,
                'total_days' => $newDays,
                'reason' => $data['reason'] ?? $leave->reason,
            ]);

            return $leave->fresh(['user.profile', 'employee.user.profile', 'approver.profile', 'flow.steps.role']);
        });
    }

    public function deletePendingLeave(Leave $leave): void
    {
        if ($leave->type === Leave::TYPE_ANNUAL) {
            $this->releaseAnnualLeave($leave);
        }

        $leave->delete();
    }

    private function getOrCreateAnnualBalance(int $employeeId, int $year, ?LeavePolicy $policy = null): EmployeeLeaveBalance
    {
        $policy ??= LeavePolicy::where('year', $year)->first() ?? LeavePolicy::firstOrCreate(
            ['year' => $year],
            ['annual_allowance' => 12, 'carry_over_allowance' => 0, 'max_pending_days' => 30, 'active' => true]
        );

        $previousYearBalance = EmployeeLeaveBalance::where('employee_id', $employeeId)
            ->where('year', $year - 1)
            ->where('leave_type', Leave::TYPE_ANNUAL)
            ->first();

        $carryOver = 0;
        if ($previousYearBalance) {
            $carryOver = min(
                $policy->carry_over_allowance,
                max(0, ($previousYearBalance->allocated_days + $previousYearBalance->carry_over_days) - $previousYearBalance->used_days)
            );
        }

        return EmployeeLeaveBalance::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'year' => $year,
                'leave_type' => Leave::TYPE_ANNUAL,
            ],
            [
                'leave_policy_id' => $policy->id,
                'allocated_days' => $policy->annual_allowance,
                'carry_over_days' => $carryOver,
                'used_days' => 0,
                'pending_days' => 0,
            ]
        );
    }

    private function finalizeAnnualLeave(Leave $leave): void
    {
        if ($leave->type !== Leave::TYPE_ANNUAL) {
            return;
        }

        $year = (int) Carbon::parse($leave->start_date)->format('Y');
        $balance = $this->getOrCreateAnnualBalance($leave->employee_id, $year);

        $balance->decrement('pending_days', $leave->total_days);
        $balance->increment('used_days', $leave->total_days);
    }

    private function releaseAnnualLeave(Leave $leave): void
    {
        if ($leave->type !== Leave::TYPE_ANNUAL) {
            return;
        }

        $year = (int) Carbon::parse($leave->start_date)->format('Y');
        $balance = $this->getOrCreateAnnualBalance($leave->employee_id, $year);

        if ($balance->pending_days >= $leave->total_days) {
            $balance->decrement('pending_days', $leave->total_days);
        }
    }
}
