<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    /**
     * GET /departments - List all departments with pagination
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('department.view')) {
            return ApiResponse::error('Forbidden', 'Insufficient permissions', 403);
        }

        try {
            $validated = $request->validate([
                'per_page' => 'sometimes|integer|min:1|max:100',
                'search'   => 'sometimes|string|max:255',
            ]);

            $perPage = $validated['per_page'] ?? 10;
            $search = $validated['search'] ?? null;

            $query = Department::withCount('employees');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('code', 'like', '%' . $search . '%');
                });
            }

            $departments = $query->latest('id')->paginate($perPage)->withQueryString();

            return ApiResponse::success(
                $departments->isEmpty() ? 'No departments available' : 'Department list',
                $departments
            );

        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid query parameters', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch departments', null, 500);
        }
    }

    /**
     * POST /departments - Create new department
     */
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermission('department.create')) {
            return ApiResponse::error('Forbidden', 'Insufficient permissions', 403);
        }

        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255|unique:departments,name',
                'code'        => 'nullable|string|max:50|unique:departments,code',
                'description' => 'nullable|string|max:1000',
                'manager_id'  => 'nullable|integer|exists:users,id',
            ]);

            $department = Department::create($validated);
            $department->load('manager');

            return ApiResponse::success('Department created successfully', $department, 201);

        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create department', null, 500);
        }
    }

    /**
     * GET /departments/{id} - Show specific department
     */
    public function show(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('department.view')) {
            return ApiResponse::error('Forbidden', 'Insufficient permissions', 403);
        }

        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid department ID']);
            }

            $department = Department::with(['manager:id,name,email', 'employees:id,employee_code'])
                ->withCount('employees')
                ->findOrFail($id);

            return ApiResponse::success('Department detail', $department);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Department not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch department', null, 500);
        }
    }

    /**
     * PUT /departments/{id} - Update department
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('department.update')) {
            return ApiResponse::error('Forbidden', 'Insufficient permissions', 403);
        }

        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid department ID']);
            }

            $department = Department::findOrFail($id);

            $validated = $request->validate([
                'name'        => 'sometimes|string|max:255|unique:departments,name,' . $id,
                'code'        => 'sometimes|nullable|string|max:50|unique:departments,code,' . $id,
                'description' => 'sometimes|nullable|string|max:1000',
                'manager_id'  => 'sometimes|nullable|integer|exists:users,id',
            ]);

            $department->update($validated);
            $department->load('manager');

            return ApiResponse::success('Department updated successfully', $department->fresh());

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Department not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update department', null, 500);
        }
    }

    /**
     * DELETE /departments/{id} - Delete department
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->hasPermission('department.delete')) {
            return ApiResponse::error('Forbidden', 'Insufficient permissions', 403);
        }

        try {
            if ($id <= 0) {
                throw ValidationException::withMessages(['id' => 'Invalid department ID']);
            }

            $department = Department::findOrFail($id);

            if ($department->employees()->count() > 0) {
                return ApiResponse::error('Cannot delete department', 'Department has employees assigned', 400);
            }

            $deleted = $department->toArray();
            $department->delete();

            return ApiResponse::success('Department deleted successfully', $deleted);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Not found', 'Department not found', 404);
        } catch (ValidationException $e) {
            return ApiResponse::error('Invalid request', $e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete department', null, 500);
        }
    }
}
