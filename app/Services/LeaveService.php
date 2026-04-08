<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\ApprovalFlow;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Enums\LeaveStatus;

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

        return Leave::create([
            'user_id'          => $user->id,
            'start_date'       => $data['start_date'],
            'end_date'         => $data['end_date'],
            'reason'           => $data['reason'] ?? null,
            'status'           => LeaveStatus::Pending,
            'approval_flow_id' => $flow->id,
            'current_step'     => 1,
        ]);
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
        $query = Leave::with('user');

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
                'leave'        => $leave,
                'final'        => false,
                'action'       => 'approved',
                'current_step' => $leave->current_step,
                'next_role'    => $nextStep->role->name,
            ];
        }

        // Final approval
        $leave->update(['status' => LeaveStatus::Approved]);

        return [
            'leave'  => $leave->fresh(),
            'final'  => true,
            'action' => 'approved',
        ];
    }
}
