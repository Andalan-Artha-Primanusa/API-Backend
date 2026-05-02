<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Models\Employee;
use App\Models\EmployeeLifecycleEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = EmployeeLifecycleEvent::with([
            'employee.user',
            'initiator.user',
            'approver',
        ])
        ->where('event_type', 'promotion');

        if (!$user->isAdmin() && !$user->isHR() && !$user->isSuperAdmin()) {
            $employeeId = $user->employee?->id;
            $query->where(function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                  ->orWhere('initiated_by_id', $employeeId);
            });
        }

        $status = $request->query('status');
        $search = $request->query('search');

        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->whereHas('employee.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $promotions = $query->latest()->paginate(15);
        return ApiResponse::success('Promotions retrieved', $promotions);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'new_position' => 'required|string|max:255',
            'new_department' => 'nullable|string|max:255',
            'new_salary' => 'nullable|numeric|min:0',
            'reason' => 'required|string|max:1000',
            'effective_date' => 'required|date',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        DB::beginTransaction();
        try {
            $event = EmployeeLifecycleEvent::create([
                'employee_id' => $employee->id,
                'event_type' => 'promotion',
                'event_date' => now(),
                'from_value' => $employee->position,
                'to_value' => $validated['new_position'],
                'reason' => $validated['reason'],
                'initiated_by_id' => $user->employee?->id,
                'status' => 'pending',
                'effective_date' => $validated['effective_date'],
                'remarks' => json_encode([
                    'new_department' => $validated['new_department'] ?? null,
                    'new_salary' => $validated['new_salary'] ?? null,
                ]),
            ]);

            $event->load(['employee.user', 'initiator.user']);
            DB::commit();
            return ApiResponse::success('Pengajuan promosi berhasil dibuat', $event, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal membuat pengajuan promosi', $e->getMessage(), 500);
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $event = EmployeeLifecycleEvent::with('employee')->findOrFail($id);

        if ($event->event_type !== 'promotion') {
            return ApiResponse::error('Invalid event type', null, 400);
        }
        if ($event->status !== 'pending') {
            return ApiResponse::error('Promotion already processed', null, 400);
        }
        if (!$user->isAdmin() && !$user->isHR() && !$user->isSuperAdmin()) {
            return ApiResponse::error('Forbidden', null, 403);
        }
        if (!$event->employee) {
            return ApiResponse::error('Employee not found', null, 404);
        }

        DB::beginTransaction();
        try {
            $remarks = json_decode($event->remarks, true) ?? [];

            $employee = $event->employee;

            DB::table('employees')
                ->where('id', $employee->id)
                ->update([
                    'position' => $event->to_value,
                    'department' => !empty($remarks['new_department']) ? $remarks['new_department'] : $employee->department,
                    'salary' => !empty($remarks['new_salary']) ? $remarks['new_salary'] : $employee->salary,
                    'updated_at' => now(),
                ]);

            $event->update([
                'status' => 'approved',
                'approved_by_id' => $user->id,
                'approval_date' => now(),
            ]);
            $event->load(['employee.user', 'approver']);

            DB::commit();
            return ApiResponse::success('Promosi disetujui', $event);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal menyetujui promosi', $e->getMessage(), 500);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $event = EmployeeLifecycleEvent::findOrFail($id);

        if ($event->event_type !== 'promotion') {
            return ApiResponse::error('Invalid event type', null, 400);
        }
        if ($event->status !== 'pending') {
            return ApiResponse::error('Promotion already processed', null, 400);
        }
        if (!$user->isAdmin() && !$user->isHR() && !$user->isSuperAdmin()) {
            return ApiResponse::error('Forbidden', null, 403);
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $event->update([
            'status' => 'rejected',
            'approved_by_id' => $user->id,
            'approval_date' => now(),
            'remarks' => $validated['remarks'] ?? $event->remarks,
        ]);

        return ApiResponse::success('Promosi ditolak', $event);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $event = EmployeeLifecycleEvent::findOrFail($id);

        if ($event->event_type !== 'promotion') {
            return ApiResponse::error('Invalid event type', null, 400);
        }
        if (!$user->isAdmin() && !$user->isHR() && !$user->isSuperAdmin()) {
            return ApiResponse::error('Forbidden', null, 403);
        }
        if ($event->status !== 'pending') {
            return ApiResponse::error('Cannot delete processed promotion', null, 400);
        }

        $event->delete();
        return ApiResponse::success('Pengajuan promosi dihapus');
    }

    public function myPromotions(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        $query = EmployeeLifecycleEvent::with([
            'employee.user',
            'approver',
            'initiator.user',
        ])
        ->where('event_type', 'promotion');

        if ($user->isAdmin() || $user->isHR() || $user->isSuperAdmin()) {
            // Admin/HR can see all promotions
        } else {
            // Regular user: see promotions where they are the employee OR they initiated it
            $employeeId = $employee ? $employee->id : 0;
            $query->where(function ($q) use ($employeeId, $user) {
                $q->where('employee_id', $employeeId)
                  ->orWhere('initiated_by_id', $employeeId);
            });
        }

        $promotions = $query->latest()->get();

        $summary = [
            'total' => $promotions->count(),
            'approved' => $promotions->where('status', 'approved')->count(),
            'pending' => $promotions->where('status', 'pending')->count(),
            'rejected' => $promotions->where('status', 'rejected')->count(),
        ];

        return ApiResponse::success('My promotions', [
            'promotions' => $promotions,
            'summary' => $summary,
        ]);
    }
}
