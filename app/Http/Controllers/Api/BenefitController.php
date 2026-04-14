<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\Employee;
use App\Models\EmployeeBenefit;
use App\Traits\HasEmployee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitController extends Controller
{
    use HasEmployee;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'sometimes|string|in:allowance,insurance,reward,other',
            'is_active' => 'sometimes|boolean',
            'search' => 'sometimes|string|max:255',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Benefit::query()->latest();

        if (array_key_exists('is_active', $validated)) {
            $query->where('is_active', (bool) $validated['is_active']);
        }

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        return ApiResponse::success('Benefits retrieved successfully', $query->paginate($validated['per_page'] ?? 15));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeManage($request);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:benefits,code',
            'name' => 'required|string|max:255',
            'type' => 'sometimes|string|in:allowance,insurance,reward,other',
            'default_amount' => 'nullable|numeric|min:0',
            'is_taxable' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string|max:5000',
        ]);

        $benefit = Benefit::create([
            ...$validated,
            'type' => $validated['type'] ?? Benefit::TYPE_OTHER,
            'is_taxable' => $validated['is_taxable'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return ApiResponse::success('Benefit created successfully', $benefit, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $benefit = Benefit::with('employeeBenefits.employee.user.profile')->find($id);

        if (!$benefit) {
            return ApiResponse::error('Benefit not found', null, 404);
        }

        return ApiResponse::success('Benefit detail', $benefit);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $benefit = Benefit::find($id);

        if (!$benefit) {
            return ApiResponse::error('Benefit not found', null, 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:benefits,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:allowance,insurance,reward,other',
            'default_amount' => 'sometimes|nullable|numeric|min:0',
            'is_taxable' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string|max:5000',
        ]);

        $benefit->update($validated);

        return ApiResponse::success('Benefit updated successfully', $benefit->fresh());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $benefit = Benefit::withCount('employeeBenefits')->find($id);

        if (!$benefit) {
            return ApiResponse::error('Benefit not found', null, 404);
        }

        $deleted = $benefit->toArray();
        $benefit->delete();

        return ApiResponse::success('Benefit deleted successfully', $deleted);
    }

    public function assignToEmployee(Request $request, int $id): JsonResponse
    {
        $this->authorizeManage($request);

        $benefit = Benefit::find($id);

        if (!$benefit) {
            return ApiResponse::error('Benefit not found', null, 404);
        }

        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after_or_equal:effective_from',
            'custom_amount' => 'nullable|numeric|min:0',
            'status' => 'sometimes|string|in:active,inactive',
            'notes' => 'nullable|string|max:5000',
        ]);

        $assignment = EmployeeBenefit::create([
            'employee_id' => $validated['employee_id'],
            'benefit_id' => $benefit->id,
            'effective_from' => $validated['effective_from'],
            'effective_to' => $validated['effective_to'] ?? null,
            'custom_amount' => $validated['custom_amount'] ?? null,
            'status' => $validated['status'] ?? EmployeeBenefit::STATUS_ACTIVE,
            'notes' => $validated['notes'] ?? null,
            'assigned_by' => $request->user()->id,
        ]);

        return ApiResponse::success('Benefit assigned successfully', $assignment->load(['benefit', 'employee.user.profile', 'assigner:id,name,email']), 201);
    }

    public function employeeBenefits(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return ApiResponse::error('Employee not found', null, 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|string|in:active,inactive',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = EmployeeBenefit::with(['benefit', 'assigner:id,name,email'])
            ->where('employee_id', $employeeId)
            ->latest();

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return ApiResponse::success('Employee benefits retrieved successfully', $query->paginate($validated['per_page'] ?? 15));
    }

    public function myBenefits(Request $request): JsonResponse
    {
        $employee = $this->getAuthenticatedEmployee();

        $query = EmployeeBenefit::with(['benefit', 'assigner:id,name,email'])
            ->where('employee_id', $employee->id)
            ->latest();

        return ApiResponse::success('My benefits retrieved successfully', $query->paginate($request->integer('per_page', 15)));
    }

    private function authorizeManage(Request $request): void
    {
        $user = $request->user();

        if (!($user->isAdmin() || $user->isHR())) {
            abort(403, 'No permission');
        }
    }
}
