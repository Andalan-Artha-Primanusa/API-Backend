<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeavePolicyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $policies = LeavePolicy::latest('year')->get();

        return ApiResponse::success('Leave policies retrieved successfully', $policies);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100|unique:leave_policies,year',
            'annual_allowance' => 'sometimes|integer|min:0|max:365',
            'carry_over_allowance' => 'sometimes|integer|min:0|max:365',
            'max_pending_days' => 'sometimes|integer|min:0|max:365',
            'active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);

        $policy = LeavePolicy::create($validated);

        return ApiResponse::success('Leave policy created successfully', $policy, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $policy = LeavePolicy::find($id);

        if (!$policy) {
            return ApiResponse::error('Leave policy not found', null, 404);
        }

        $validated = $request->validate([
            'year' => 'sometimes|integer|min:2020|max:2100|unique:leave_policies,year,' . $id,
            'annual_allowance' => 'sometimes|integer|min:0|max:365',
            'carry_over_allowance' => 'sometimes|integer|min:0|max:365',
            'max_pending_days' => 'sometimes|integer|min:0|max:365',
            'active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string',
        ]);

        $policy->update($validated);

        return ApiResponse::success('Leave policy updated successfully', $policy->fresh());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $policy = LeavePolicy::find($id);

        if (!$policy) {
            return ApiResponse::error('Leave policy not found', null, 404);
        }

        $deleted = $policy->toArray();
        $policy->delete();

        return ApiResponse::success('Leave policy deleted successfully', $deleted);
    }
}