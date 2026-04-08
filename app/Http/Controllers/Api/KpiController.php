<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kpi;
use App\Models\Employee;

class KpiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 🔥 HR / MANAGER
    |--------------------------------------------------------------------------
    */

    // ✅ GET ALL KPI
    public function index()
    {
        $kpis = Kpi::with('employee')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $kpis
        ]);
    }

    // ✅ CREATE KPI
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'target' => 'required|numeric',
            'period' => 'required|string'
        ]);

        $kpi = Kpi::create([
            'employee_id' => $request->employee_id,
            'title' => $request->title,
            'description' => $request->description,
            'target' => $request->target,
            'achievement' => 0,
            'score' => 0,
            'status' => 'draft',
            'period' => $request->period,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'KPI berhasil dibuat',
            'data' => $kpi
        ]);
    }

    // ✅ DETAIL KPI
    public function show($id)
    {
        $kpi = Kpi::with('employee')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $kpi
        ]);
    }

    // ✅ UPDATE KPI
    public function update(Request $request, $id)
    {
        $kpi = Kpi::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'target' => 'sometimes|numeric',
            'achievement' => 'sometimes|numeric'
        ]);

        $kpi->update($request->only([
            'title',
            'description',
            'target',
            'achievement'
        ]));

        // 🔥 AUTO HITUNG SCORE
        if ($kpi->target > 0) {
            $kpi->score = ($kpi->achievement / $kpi->target) * 100;
            $kpi->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'KPI berhasil diupdate',
            'data' => $kpi
        ]);
    }

    // ✅ DELETE KPI
    public function destroy($id)
    {
        $kpi = Kpi::findOrFail($id);
        $kpi->delete();

        return response()->json([
            'success' => true,
            'message' => 'KPI berhasil dihapus'
        ]);
    }

    // ✅ KPI PER EMPLOYEE
    public function byEmployee($employee_id)
    {
        $kpis = Kpi::where('employee_id', $employee_id)->get();

        return response()->json([
            'success' => true,
            'data' => $kpis
        ]);
    }

    // ✅ APPROVE KPI
    public function approve($id)
    {
        $kpi = Kpi::findOrFail($id);

        $kpi->update([
            'status' => 'approved'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'KPI berhasil disetujui'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 👤 USER (NO ROLE)
    |--------------------------------------------------------------------------
    */

    // ✅ KPI SAYA
    public function myKpi(Request $request)
    {
        $user = $request->user();

        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'User bukan employee'
            ], 403);
        }

        $kpis = Kpi::where('employee_id', $employee->id)->get();

        return response()->json([
            'success' => true,
            'data' => $kpis
        ]);
    }

    // ✅ SUBMIT KPI
    public function submit(Request $request, $id)
    {
        $user = $request->user();

        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'User bukan employee'
            ], 403);
        }

        $kpi = Kpi::findOrFail($id);

        // 🔒 VALIDASI: KPI harus milik user
        if ($kpi->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak punya akses ke KPI ini'
            ], 403);
        }

        // 🔒 OPTIONAL: hanya draft yang bisa disubmit
        if ($kpi->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'KPI sudah disubmit / diproses'
            ], 400);
        }

        $kpi->update([
            'status' => 'submitted'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'KPI berhasil disubmit'
        ]);
    }
}