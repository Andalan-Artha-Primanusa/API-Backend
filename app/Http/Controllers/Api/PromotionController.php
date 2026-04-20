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
    /**
     * Pengajuan promosi jabatan (oleh manager/HR)
     */
    public function promote(Request $request, int $employeeId): JsonResponse
    {
        $employee = Employee::findOrFail($employeeId);
        $user = $request->user();

        $validated = $request->validate([
            'new_position' => 'required|string|max:255',
            'new_department' => 'nullable|string|max:255',
            'new_salary' => 'nullable|numeric|min:0',
            'reason' => 'required|string|max:1000',
            'effective_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $event = EmployeeLifecycleEvent::create([
                'employee_id' => $employee->id,
                'event_type' => 'promotion',
                'event_date' => now(),
                'from_value' => $employee->position,
                'to_value' => $validated['new_position'],
                'reason' => $validated['reason'],
                'initiated_by_id' => $user->employee->id ?? null,
                'status' => 'pending',
                'effective_date' => $validated['effective_date'],
            ]);
            // (Approval flow & notification bisa ditambah di sini)
            DB::commit();
            return ApiResponse::success('Pengajuan promosi berhasil dibuat', $event, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Gagal membuat pengajuan promosi', $e->getMessage(), 500);
        }
    }
}
