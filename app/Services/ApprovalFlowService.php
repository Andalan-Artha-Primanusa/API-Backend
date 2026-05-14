<?php

namespace App\Services;

use App\Models\ApprovalFlow;
use App\Models\ApprovalFlowHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApprovalFlowService
{
    /**
     * Apply approval flow to a newly created model.
     * Sets approval_flow_id and current_step = 1.
     *
     * @throws \RuntimeException if no approval flow is configured for the module
     */
    public function applyToModel(string $module, Model $model): Model
    {
        $flow = ApprovalFlow::where('module', $module)->where('is_active', true)->with('steps.role', 'steps.user')->first();

        if (!$flow) {
            throw new \RuntimeException("Approval flow for '{$module}' has not been configured.");
        }

        $model->approval_flow_id = $flow->id;
        $model->current_step = 1;
        $model->save();

        // Record initial pending history for first step
        $firstStep = $flow->steps->where('step_order', 1)->first();
        if ($firstStep) {
            ApprovalFlowHistory::create([
                'module' => $module,
                'module_id' => $model->id,
                'approval_flow_id' => $flow->id,
                'step_order' => 1,
                'role_id' => $firstStep->role_id,
                'user_id' => $firstStep->user_id,
                'action' => 'pending',
                'acted_at' => now(),
            ]);
        }

        return $model;
    }

    /**
     * Process an approval action for a model at its current step.
     *
     * @param Model $model Must have approvalFlow, current_step properties and approvalFlow() relationship
     * @param User $approver
     * @param string $action 'approved' or 'rejected'
     * @param string|null $note
     * @return array{model: Model, final: bool, action: string, current_step?: int, next_role?: string}
     *
     * @throws \DomainException for business rule violations
     * @throws \RuntimeException for system configuration issues
     */
    public function processApproval(Model $model, User $approver, string $action, ?string $note = null): array
    {
        $flow = $model->approvalFlow;

        if (!$flow) {
            throw new \RuntimeException('Approval flow not found on this model.');
        }

        $flow->loadMissing('steps.role', 'steps.user');

        $step = $flow->steps->where('step_order', $model->current_step)->first();

        if (!$step) {
            throw new \RuntimeException('Approval step not found.');
        }

        // Check if user has the required role
        if (!$approver->hasRole($step->role->name)) {
            throw new \DomainException('Bukan giliran Anda untuk menyetujui permintaan ini.');
        }

        // Check if specific user is assigned to this step
        if ($step->user_id && $step->user_id !== $approver->id) {
            throw new \DomainException('Approver khusus telah ditugaskan untuk langkah ini.');
        }

        // Record approval history
        ApprovalFlowHistory::create([
            'module' => $flow->module,
            'module_id' => $model->id,
            'approval_flow_id' => $flow->id,
            'step_order' => $step->step_order,
            'role_id' => $step->role_id,
            'user_id' => $approver->id,
            'action' => $action,
            'note' => $note,
            'acted_at' => now(),
        ]);

        // Rejection — finalize immediately
        if ($action === 'rejected') {
            $model->status = 'rejected';
            $model->save();

            return [
                'model' => $model->fresh(),
                'final' => true,
                'action' => 'rejected',
            ];
        }

        // Check if there's a next step
        $nextStep = $flow->steps->where('step_order', $model->current_step + 1)->first();

        if ($nextStep) {
            $model->current_step = $model->current_step + 1;
            $model->save();

            // Record pending for next step
            ApprovalFlowHistory::create([
                'module' => $flow->module,
                'module_id' => $model->id,
                'approval_flow_id' => $flow->id,
                'step_order' => $nextStep->step_order,
                'role_id' => $nextStep->role_id,
                'user_id' => $nextStep->user_id,
                'action' => 'pending',
                'acted_at' => now(),
            ]);

            $model->refresh();

            return [
                'model' => $model,
                'final' => false,
                'action' => 'approved',
                'current_step' => $model->current_step,
                'next_role' => $nextStep->role->name,
            ];
        }

        // No next step — final approval
        $model->status = 'approved';
        $model->save();

        return [
            'model' => $model->fresh(),
            'final' => true,
            'action' => 'approved',
        ];
    }

    /**
     * Get approval history for a specific module item.
     */
    public function getHistory(string $module, int $moduleId)
    {
        return ApprovalFlowHistory::where('module', $module)
            ->where('module_id', $moduleId)
            ->with(['role', 'user.employee', 'flow'])
            ->orderBy('step_order')
            ->orderBy('acted_at')
            ->get();
    }

    /**
     * Check if the given user can act on the current step of the model.
     * Returns true if no flow is configured (anyone can act).
     */
    public function canUserAct(Model $model, User $user): bool
    {
        if (!$model->approval_flow_id || !$model->approvalFlow) {
            return true;
        }

        $flow = $model->approvalFlow;
        $flow->loadMissing('steps.role', 'steps.user');

        $step = $flow->steps->where('step_order', $model->current_step)->first();
        if (!$step) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$user->hasRole($step->role->name)) {
            return false;
        }

        if ($step->user_id && $step->user_id !== $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Add can_act flag to a collection of models for the given user.
     * Also eager-loads approvalFlow.steps.role if not already loaded.
     */
    public function addCanActToListings(Collection $items, User $user): Collection
    {
        return $items->map(function ($item) use ($user) {
            if ($item instanceof Model && $item->relationLoaded('approvalFlow')) {
                $item->can_act = $this->canUserAct($item, $user);
            } else {
                $item->can_act = true;
            }
            return $item;
        });
    }
}
