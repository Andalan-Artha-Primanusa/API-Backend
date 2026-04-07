<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // 🔥 GET EMPLOYEE
    public function index(Request $request)
    {
        $user = $request->user();

        // EMPLOYEE → lihat data sendiri
        if ($user->isEmployee()) {

            if (!$user->employee) {
                return ApiResponse::error(
                    'Employee belum dibuat',
                    'Employee data belum tersedia',
                    404
                );
            }

            return ApiResponse::success(
                'Data employee sendiri',
                $user->employee
            );
        }

        // HR / ADMIN → lihat semua
        if ($user->isHR() || $user->isAdmin()) {

            $query = Employee::with('user');

            // 🔍 FILTER
            if ($request->has('department')) {
                $query->where('department', $request->department);
            }

            // 🔎 SEARCH
            if ($request->has('search')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }

            // ↕️ SORT
            $sort = $request->get('sort', 'id');
            $order = $request->get('order', 'asc');

            $query->orderBy($sort, $order);

            return ApiResponse::success(
                'Data semua employee',
                $query->paginate(5)
            );
        }

        return ApiResponse::error('Forbidden', 'Unauthorized', 403);
    }

    // 🔥 CREATE EMPLOYEE
    public function store(Request $request)
    {
        if (!($request->user()->isHR() || $request->user()->isAdmin())) {
            return ApiResponse::error('Forbidden', 'Unauthorized', 403);
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

        return ApiResponse::success(
            'Employee berhasil dibuat',
            $employee,
            201
        );
    }

    // 🔥 DETAIL
    public function show($id, Request $request)
    {
        $user = $request->user();
        $employee = Employee::with('user')->findOrFail($id);

        if ($user->isEmployee() && $employee->user_id !== $user->id) {
            return ApiResponse::error('Forbidden', 'Unauthorized', 403);
        }

        return ApiResponse::success(
            'Detail employee',
            $employee
        );
    }

    // 🔥 UPDATE
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        if (!($request->user()->isHR() || $request->user()->isAdmin())) {
            return ApiResponse::error('Forbidden', 'Unauthorized', 403);
        }

        $employee->update($request->only([
            'position',
            'department',
            'salary'
        ]));

        return ApiResponse::success(
            'Employee berhasil diupdate',
            $employee
        );
    }

    // 🔥 DELETE
    public function destroy($id, Request $request)
    {
        if (!$request->user()->isSuperAdmin()) {
            return ApiResponse::error('Forbidden', 'Unauthorized', 403);
        }

        Employee::findOrFail($id)->delete();

        return ApiResponse::success('Employee berhasil dihapus');
    }
}
