<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Asset as InventoryAsset;
use App\Models\AssetAssignment as InventoryAssetAssignment;
use App\Models\Employee;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'status' => 'sometimes|string|in:available,assigned,maintenance,retired',
            'condition' => 'sometimes|string|in:new,good,fair,damaged,retired',
            'search' => 'sometimes|string|max:255',
        ]);

        $query = InventoryAsset::with('assignments.employee.user')->latest();

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['condition'])) {
            $query->where('condition', $validated['condition']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('brand', 'like', '%' . $search . '%')
                    ->orWhere('model', 'like', '%' . $search . '%')
                    ->orWhere('serial_number', 'like', '%' . $search . '%');
            });
        }

        return ApiResponse::success('Assets retrieved successfully', $query->paginate($validated['per_page'] ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:assets,code',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number',
            'condition' => 'sometimes|string|in:new,good,fair,damaged,retired',
            'status' => 'sometimes|string|in:available,assigned,maintenance,retired',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $asset = InventoryAsset::create($validated);

        return ApiResponse::success('Asset created successfully', $asset, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $asset = InventoryAsset::with('assignments.employee.user.profile', 'assignments.assignedBy:id,name,email')->find($id);

        if (!$asset) {
            return ApiResponse::error('Asset not found', null, 404);
        }

        $user = $request->user();
        if (!($user->isAdmin() || $user->isHR())) {
            $employee = $user->employee;
            $hasAssignment = $asset->assignments->contains(fn ($assignment) => $assignment->employee_id === $employee?->id && $assignment->status === 'assigned');

            if (!$hasAssignment) {
                return ApiResponse::error('Forbidden', 'No permission', 403);
            }
        }

        return ApiResponse::success('Asset detail', $asset);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $asset = InventoryAsset::find($id);

        if (!$asset) {
            return ApiResponse::error('Asset not found', null, 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|string|max:100|unique:assets,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|nullable|string|max:255',
            'brand' => 'sometimes|nullable|string|max:255',
            'model' => 'sometimes|nullable|string|max:255',
            'serial_number' => 'sometimes|nullable|string|max:255|unique:assets,serial_number,' . $id,
            'condition' => 'sometimes|string|in:new,good,fair,damaged,retired',
            'status' => 'sometimes|string|in:available,assigned,maintenance,retired',
            'purchase_date' => 'sometimes|nullable|date',
            'purchase_price' => 'sometimes|numeric|min:0',
            'notes' => 'sometimes|nullable|string',
        ]);

        $asset->update($validated);

        return ApiResponse::success('Asset updated successfully', $asset->fresh());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $asset = InventoryAsset::with('assignments')->find($id);

        if (!$asset) {
            return ApiResponse::error('Asset not found', null, 404);
        }

        $deleted = $asset->toArray();
        $asset->delete();

        return ApiResponse::success('Asset deleted successfully', $deleted);
    }

    public function assign(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'assignment_note' => 'nullable|string',
            'assigned_at' => 'nullable|date',
        ]);

        $asset = InventoryAsset::find($id);

        if (!$asset) {
            return ApiResponse::error('Asset not found', null, 404);
        }

        if ($asset->status === 'retired') {
            return ApiResponse::error('Asset is retired', null, 400);
        }

        if (InventoryAssetAssignment::where('asset_id', $asset->id)->where('status', 'assigned')->exists()) {
            return ApiResponse::error('Asset already assigned', null, 400);
        }

        $assignment = InventoryAssetAssignment::create([
            'asset_id' => $asset->id,
            'employee_id' => $validated['employee_id'],
            'assigned_by' => $user->id,
            'assigned_at' => $validated['assigned_at'] ?? now(),
            'status' => 'assigned',
            'assignment_note' => $validated['assignment_note'] ?? null,
        ]);

        $asset->update(['status' => 'assigned']);

        $employee = Employee::with('user')->find($validated['employee_id']);
        if ($employee?->user) {
            UserNotification::create([
                'user_id' => $employee->user_id,
                'sender_user_id' => $user->id,
                'title' => 'Asset assigned',
                'message' => 'An asset has been assigned to you: ' . $asset->name,
                'type' => 'asset.assigned',
                'category' => 'asset_management',
                'data' => [
                    'asset_id' => $asset->id,
                    'asset_code' => $asset->code,
                    'asset_name' => $asset->name,
                ],
            ]);
        }

        return ApiResponse::success('Asset assigned successfully', $assignment->load('asset', 'employee.user.profile', 'assignedBy:id,name,email'), 201);
    }

    public function returnAsset(Request $request, int $assignmentId): JsonResponse
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'return_note' => 'nullable|string',
            'returned_at' => 'nullable|date',
            'condition' => 'nullable|string|in:new,good,fair,damaged,retired',
        ]);

        $assignment = InventoryAssetAssignment::with('asset', 'employee.user')->find($assignmentId);

        if (!$assignment) {
            return ApiResponse::error('Assignment not found', null, 404);
        }

        if ($assignment->status !== 'assigned') {
            return ApiResponse::error('Asset is not currently assigned', null, 400);
        }

        $assignment->update([
            'status' => 'returned',
            'returned_at' => $validated['returned_at'] ?? now(),
            'return_note' => $validated['return_note'] ?? null,
        ]);

        $assignment->asset->update([
            'status' => $validated['condition'] === 'retired' ? 'retired' : 'available',
            'condition' => $validated['condition'] ?? $assignment->asset->condition,
        ]);

        if ($assignment->employee?->user) {
            UserNotification::create([
                'user_id' => $assignment->employee->user_id,
                'sender_user_id' => $user->id,
                'title' => 'Asset returned',
                'message' => 'Asset return has been processed for: ' . $assignment->asset->name,
                'type' => 'asset.returned',
                'category' => 'asset_management',
                'data' => [
                    'asset_id' => $assignment->asset->id,
                    'asset_code' => $assignment->asset->code,
                    'asset_name' => $assignment->asset->name,
                ],
            ]);
        }

        return ApiResponse::success('Asset returned successfully', $assignment->fresh(['asset', 'employee.user.profile', 'assignedBy:id,name,email']));
    }

    public function myAssets(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return ApiResponse::error('Employee record not found', null, 404);
        }

        $assignments = InventoryAssetAssignment::with(['asset', 'assignedBy:id,name,email'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return ApiResponse::success('My assets retrieved successfully', $assignments);
    }
}