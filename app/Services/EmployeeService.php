<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class EmployeeService
{
    /**
     * Get a filtered, searched, and sorted employee list.
     */
    public function getFilteredList(Request $request): LengthAwarePaginator
    {
        $query = Employee::with(['user.profile', 'manager.profile']);

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        // Search by name or email through user relation
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort with whitelist
        $allowedSort = ['id', 'department', 'salary', 'hire_date', 'employee_code'];
        $sort = in_array($request->get('sort'), $allowedSort)
            ? $request->get('sort')
            : 'id';
        $order = $request->get('order') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sort, $order);

        return $query->paginate(15);
    }

    /**
     * Find an employee with its user relation.
     */
    public function findWithUser(int|string $id): Employee
    {
        return Employee::with(['user.profile', 'manager.profile'])->findOrFail($id);
    }

    /**
     * Create a new employee record.
     */
    public function create(array $data): Employee
    {
        return Employee::create($data)->load(['user.profile', 'manager.profile']);
    }

    /**
     * Update an existing employee record.
     */
    public function update(int|string $id, array $data): Employee
    {
        $employee = Employee::findOrFail($id);
        $employee->update($data);

        return $employee->fresh(['user.profile', 'manager.profile']);
    }

    /**
     * Delete an employee record.
     */
    public function delete(int|string $id): void
    {
        Employee::findOrFail($id)->delete();
    }
}
