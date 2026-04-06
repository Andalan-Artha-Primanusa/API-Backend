<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // 🔥 GET EMPLOYEE
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('employee')) {
            return response()->json($user->employee);
        }

        if ($user->hasAnyRole(['hr', 'admin', 'super_admin'])) {
            return response()->json(
                Employee::with('user')->get()
            );
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }

    // 🔥 CREATE EMPLOYEE (HR ONLY)
    public function store(Request $request)
    {
        if (! $request->user()->hasAnyRole(['hr', 'admin', 'super_admin'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'employee_code' => 'required|unique:employees',
            'position' => 'required|string',
            'department' => 'required|string',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric',
        ]);

        $employee = Employee::create($data);

        return response()->json([
            'message' => 'Employee berhasil dibuat',
            'data' => $employee
        ], 201);
    }

    // 🔥 DETAIL
    public function show($id, Request $request)
    {
        $user = $request->user();
        $employee = Employee::with('user')->findOrFail($id);

        if ($user->hasRole('employee') && $employee->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($employee);
    }

    // 🔥 UPDATE
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        if (! $request->user()->hasAnyRole(['hr', 'admin', 'super_admin'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $employee->update($request->only([
            'position',
            'department',
            'salary'
        ]));

        return response()->json([
            'message' => 'Employee updated',
            'data' => $employee
        ]);
    }

    // 🔥 DELETE
    public function destroy($id, Request $request)
    {
        if (! $request->user()->hasRole('super_admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        Employee::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Employee deleted'
        ]);
    }
}
