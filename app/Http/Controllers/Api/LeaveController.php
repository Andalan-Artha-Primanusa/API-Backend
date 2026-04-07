<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Leave;
use Illuminate\Http\Request;
use App\Models\ApprovalFlow;

class LeaveController extends Controller
{
    //  CREATE LEAVE
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->hasPermission('leave.create')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
        ]);

        //  ambil flow (module = leave)
        $flow = ApprovalFlow::where('module', 'leave')->first();

        if (!$flow) {
            return ApiResponse::error('Flow belum diset', null, 500);
        }

        $leave = Leave::create([
            'user_id' => $user->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'] ?? null,
            'status' => Leave::STATUS_PENDING,
            'approval_flow_id' => $flow->id,
            'current_step' => 1,
        ]);

        return ApiResponse::success('Leave diajukan', $leave, 201);
    }

    //  GET LEAVE
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->hasPermission('leave.view')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        // EMPLOYEE → hanya milik sendiri
        if ($user->isEmployee()) {
            return ApiResponse::success(
                'Leave milik sendiri',
                Leave::with('user')
                    ->where('user_id', $user->id)
                    ->get()
            );
        }

        // MANAGER → hanya bawahan
        if ($user->isManager()) {
            $employeeIds = $user->teamMembers()->pluck('user_id');

            return ApiResponse::success(
                'Leave bawahan',
                Leave::with('user')
                    ->whereIn('user_id', $employeeIds)
                    ->get()
            );
        }

        // HR / ADMIN → semua
        return ApiResponse::success(
            'Semua leave',
            Leave::with('user')->get()
        );
    }

    //  APPROVE / REJECT (DYNAMIC FLOW)
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $leave = Leave::with('flow.steps.role')->findOrFail($id);

        //  cek flow
        if (!$leave->flow) {
            return ApiResponse::error('Flow tidak ditemukan', null, 500);
        }

        //  permission
        if (!$user->hasPermission('leave.approve')) {
            return ApiResponse::error('Forbidden', 'No permission', 403);
        }

        //  status harus pending
        if ($leave->status !== Leave::STATUS_PENDING) {
            return ApiResponse::error('Leave sudah diproses', null, 400);
        }

        $request->validate([
            'status' => ['required', 'in:approved,rejected']
        ]);

        $currentStep = $leave->current_step;

        //  ambil step sekarang
        $step = $leave->flow->steps
            ->where('step_order', $currentStep)
            ->first();

        if (!$step) {
            return ApiResponse::error('Step tidak ditemukan', null, 500);
        }

        //  cek role user sesuai step
        if (!$user->hasRole($step->role->name)) {
            return ApiResponse::error('Bukan giliran anda approve', null, 403);
        }

        //  REJECT → selesai langsung
        if ($request->status === 'rejected') {
            $leave->update([
                'status' => Leave::STATUS_REJECTED
            ]);

            return ApiResponse::success('Leave ditolak', $leave);
        }

        //  cek next step
        $nextStep = $leave->flow->steps
            ->where('step_order', $currentStep + 1)
            ->first();

        //  kalau masih ada step berikutnya
        if ($nextStep) {
            $leave->update([
                'current_step' => $currentStep + 1
            ]);

            return ApiResponse::success(
                'Approved step ' . $currentStep . ', lanjut ke step ' . ($currentStep + 1),
                [
                    'leave' => $leave,
                    'current_step' => $leave->current_step,
                    'next_role' => $nextStep->role->name
                ]
            );
        }

        //  FINAL APPROVE
        $leave->update([
            'status' => Leave::STATUS_APPROVED
        ]);

        return ApiResponse::success(
            'Leave disetujui final',
            [
                'leave' => $leave,
                'final' => true
            ]
        );
    }
}
