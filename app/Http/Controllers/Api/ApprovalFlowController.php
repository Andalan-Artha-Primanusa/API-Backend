<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalFlowController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $flows = ApprovalFlow::with('steps.role')
            ->withCount('steps')
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        return ApiResponse::success('Approval flows retrieved successfully', $flows);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $flow = ApprovalFlow::with('steps.role')->find($id);

        if (!$flow) {
            return ApiResponse::error('Approval flow not found', null, 404);
        }

        return ApiResponse::success('Approval flow detail', $flow);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:100|unique:approval_flows,module',
            'steps' => 'required|array|min:1',
            'steps.*.step_order' => 'required|integer|min:1|distinct',
            'steps.*.role_id' => 'required|exists:roles,id|distinct',
        ]);

        $flow = DB::transaction(function () use ($validated) {
            $flow = ApprovalFlow::create([
                'name' => $validated['name'],
                'module' => $validated['module'],
            ]);

            foreach ($validated['steps'] as $step) {
                ApprovalStep::create([
                    'approval_flow_id' => $flow->id,
                    'step_order' => $step['step_order'],
                    'role_id' => $step['role_id'],
                ]);
            }

            return $flow->load('steps.role');
        });

        return ApiResponse::success('Approval flow created successfully', $flow, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $flow = ApprovalFlow::with('steps.role')->find($id);

        if (!$flow) {
            return ApiResponse::error('Approval flow not found', null, 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'module' => 'sometimes|string|max:100|unique:approval_flows,module,' . $flow->id,
            'steps' => 'sometimes|array|min:1',
            'steps.*.step_order' => 'required_with:steps|integer|min:1|distinct',
            'steps.*.role_id' => 'required_with:steps|exists:roles,id|distinct',
        ]);

        $flow = DB::transaction(function () use ($flow, $validated) {
            $flow->update(collect($validated)->only(['name', 'module'])->filter()->toArray());

            if (array_key_exists('steps', $validated)) {
                $flow->steps()->delete();

                foreach ($validated['steps'] as $step) {
                    ApprovalStep::create([
                        'approval_flow_id' => $flow->id,
                        'step_order' => $step['step_order'],
                        'role_id' => $step['role_id'],
                    ]);
                }
            }

            return $flow->fresh('steps.role');
        });

        return ApiResponse::success('Approval flow updated successfully', $flow);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && !$user->isHR()) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $flow = ApprovalFlow::with('steps.role')->find($id);

        if (!$flow) {
            return ApiResponse::error('Approval flow not found', null, 404);
        }

        $deleted = $flow->toArray();
        $flow->delete();

        return ApiResponse::success('Approval flow deleted successfully', $deleted);
    }
}