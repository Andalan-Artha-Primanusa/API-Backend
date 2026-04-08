<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
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

        return ApiResponse::success('Own employee data', $user->employee->load('user'));
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

        $this->employeeService->delete($id);

        return ApiResponse::success('Employee deleted successfully');
    }
}
