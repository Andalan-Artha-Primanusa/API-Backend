<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class LeaveController extends Controller
{
    // 🔥 HELPER BIAR GAK ERROR 500
    private function getEmployeeId()
    {
        $user = auth()->user();

        if (!$user || !$user->employee) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'User bukan employee / belum punya employee'
            ], 400));
        }

        return $user->employee->id;
    }

    // 📌 LIST + SISA CUTI
    public function index()
    {
        $employeeId = $this->getEmployeeId();

        $total = 12;

        $used = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->sum('total_days');

        return response()->json([
            'success' => true,
            'remaining_leave' => $total - $used,
            'used_leave' => $used,
            'data' => Leave::where('employee_id', $employeeId)->latest()->get()
        ]);
    }

    // 📌 MY LEAVES
    public function myLeaves()
    {
        $employeeId = $this->getEmployeeId();

        return Leave::where('employee_id', $employeeId)
            ->latest()
            ->get();
    }

    // 📌 BALANCE
    public function balance()
    {
        $employeeId = $this->getEmployeeId();

        $total = 12;

        $used = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->sum('total_days');

        return response()->json([
            'total' => $total,
            'used' => $used,
            'remaining' => $total - $used
        ]);
    }

    // 📌 STORE (AJUKAN CUTI)
    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'type'       => 'required|string',
            'reason'     => 'nullable|string'
        ]);

        $employeeId = $this->getEmployeeId();

        // 🔥 CEK BENTROK
        $exists = Leave::where('employee_id', $employeeId)
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            })->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal cuti bentrok'
            ], 400);
        }

        // 🔥 HITUNG HARI (AMAN)
        $days = Carbon::parse($request->start_date)
            ->diffInDays(Carbon::parse($request->end_date)) + 1;

        $leave = Leave::create([
            'employee_id' => $employeeId,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'total_days'  => $days,
            'type'        => $request->type,
            'reason'      => $request->reason,
            'status'      => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan cuti berhasil',
            'data' => $leave
        ]);
    }

    // 📌 DETAIL
    public function show($id)
    {
        return Leave::findOrFail($id);
    }

    // 📌 DELETE
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);

        if ($leave->status === 'approved') {
            return response()->json([
                'message' => 'Cuti yang sudah disetujui tidak bisa dihapus'
            ], 400);
        }

        $leave->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuti berhasil dihapus'
        ]);
    }

    // 📌 PENDING (ATASAN)
    public function pending()
    {
        return Leave::where('status', 'pending')
            ->with('employee')
            ->get();
    }

    // 📌 APPROVE
    public function approve(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_note' => $request->note
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuti disetujui'
        ]);
    }

    // 📌 REJECT
    public function reject(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_note' => $request->note
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuti ditolak'
        ]);
    }

    // 📌 CALENDAR
    public function calendar()
    {
        $employeeId = $this->getEmployeeId();

        $leaves = Leave::where('employee_id', $employeeId)->get();

        return $leaves->map(function ($leave) {
            return [
                'title' => strtoupper($leave->type),
                'start' => $leave->start_date,
                'end'   => $leave->end_date,
                'status'=> $leave->status
            ];
        });
    }
}