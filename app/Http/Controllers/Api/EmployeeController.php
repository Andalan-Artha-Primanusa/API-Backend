<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\UserNotification;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    /**
     * List employees with filtering, search, and sorting.
     * Authorization order: admin/HR → manager → employee (most privileged first).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Admin/HR — full list with filtering
        if ($user->hasPermission('employee.view')) {
            $data = $this->employeeService->getFilteredList($request);
            return ApiResponse::success('All employees', $data);
        }

        // Employee — own data only (fallback)
        if (!$user->employee) {
            return ApiResponse::error(
                'Employee record not found',
                'No employee data available for this user',
                404
            );
        }

        return ApiResponse::success('Own employee data', $user->employee->load(['user.profile', 'manager.profile']));
    }

    /**
     * Create a new employee.
     * Authorization handled by StoreEmployeeRequest.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->create($request->validated());

        return ApiResponse::success('Employee created successfully', $employee, 201);
    }

    /**
     * Show employee detail.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $employee = $this->employeeService->findWithUser($id);

        // Non-owner must have employee.view permission
        if ($employee->user_id !== $user->id && !$user->hasPermission('employee.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        return ApiResponse::success('Employee detail', $employee);
    }

    /**
     * Update employee data.
     * Authorization handled by UpdateEmployeeRequest.
     */
    public function update(UpdateEmployeeRequest $request, $id): JsonResponse
    {
        $employee = $this->employeeService->update($id, $request->validated());

        return ApiResponse::success('Employee updated successfully', $employee);
    }

    /**
     * Delete an employee.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        if (!$request->user()->hasPermission('employee.delete')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $employee = $this->employeeService->findWithUser($id);
        $deleted = $employee->toArray();

        $this->employeeService->delete($id);

        return ApiResponse::success('Employee deleted successfully', $deleted);
    }

    /**
     * Start employee onboarding.
     */
    public function startOnboarding(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('employee.update')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'probation_end_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $employee = $this->employeeService->findWithUser($id);

        $employee->update([
            'status' => \App\Models\Employee::STATUS_ONBOARDING,
            'probation_end_date' => $validated['probation_end_date'] ?? null,
            'termination_date' => null,
            'termination_reason' => null,
        ]);

        UserNotification::create([
            'user_id' => $employee->user_id,
            'sender_user_id' => $request->user()->id,
            'title' => 'Onboarding started',
            'message' => 'Your onboarding process has been started. Please complete the required steps.',
            'type' => 'employee.onboarding.started',
            'category' => 'employee_lifecycle',
            'data' => [
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'status' => $employee->status,
            ],
        ]);

        return ApiResponse::success('Employee onboarding started successfully', $employee->fresh(['user.profile', 'manager.profile']));
    }

    /**
     * Mark employee as active after onboarding.
     */
    public function completeOnboarding(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('employee.update')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $employee = $this->employeeService->findWithUser($id);

        $employee->update([
            'status' => \App\Models\Employee::STATUS_ACTIVE,
        ]);

        UserNotification::create([
            'user_id' => $employee->user_id,
            'sender_user_id' => $request->user()->id,
            'title' => 'Onboarding completed',
            'message' => 'Your onboarding has been completed. Your employee account is now active.',
            'type' => 'employee.onboarding.completed',
            'category' => 'employee_lifecycle',
            'data' => [
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'status' => $employee->status,
            ],
        ]);

        return ApiResponse::success('Employee onboarding completed successfully', $employee->fresh(['user.profile', 'manager.profile']));
    }

    /**
     * Start employee offboarding.
     */
    public function offboard(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('employee.update')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'termination_date' => ['required', 'date'],
            'termination_reason' => ['required', 'string', 'max:5000'],
        ]);

        $employee = $this->employeeService->findWithUser($id);

        $employee->update([
            'status' => \App\Models\Employee::STATUS_OFFBOARDING,
            'termination_date' => $validated['termination_date'],
            'termination_reason' => $validated['termination_reason'],
        ]);

        UserNotification::create([
            'user_id' => $employee->user_id,
            'sender_user_id' => $request->user()->id,
            'title' => 'Offboarding started',
            'message' => 'Your offboarding process has been started. Please follow the clearance checklist.',
            'type' => 'employee.offboarding.started',
            'category' => 'employee_lifecycle',
            'data' => [
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'termination_date' => $employee->termination_date,
            ],
        ]);

        return ApiResponse::success('Employee offboarding started successfully', $employee->fresh(['user.profile', 'manager.profile']));
    }

    /**
     * Mark employee as terminated/inactive after clearance.
     */
    public function completeOffboarding(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('employee.update')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:inactive,terminated'],
        ]);

        $employee = $this->employeeService->findWithUser($id);

        $employee->update([
            'status' => $validated['status'] ?? \App\Models\Employee::STATUS_TERMINATED,
        ]);

        UserNotification::create([
            'user_id' => $employee->user_id,
            'sender_user_id' => $request->user()->id,
            'title' => 'Offboarding completed',
            'message' => 'Your offboarding has been completed and your employee account status has been updated.',
            'type' => 'employee.offboarding.completed',
            'category' => 'employee_lifecycle',
            'data' => [
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'status' => $employee->status,
            ],
        ]);

        return ApiResponse::success('Employee offboarding completed successfully', $employee->fresh(['user.profile', 'manager.profile']));
    }
}
