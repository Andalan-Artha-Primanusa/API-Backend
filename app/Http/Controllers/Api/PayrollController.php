<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    // 📌 GET ALL
    public function index()
    {
        $data = Payroll::with(['employee', 'details'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => $data->isEmpty()
                ? 'Data payroll belum ada'
                : 'Berhasil ambil data payroll',
            'data' => $data
        ]);
    }

    // 📌 MY PAYROLL (USER)
    public function myPayroll(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee tidak ditemukan',
                'data' => null
            ], 404);
        }

        $data = Payroll::with('details')
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => $data->isEmpty()
                ? 'Belum ada payroll'
                : 'Berhasil ambil payroll',
            'data' => $data
        ]);
    }

    // 📌 STORE (SINGLE GENERATE)
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required'
        ]);

        return DB::transaction(function () use ($request) {
            $employee = Employee::findOrFail($request->employee_id);

            if (!$employee->salary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary employee belum di set'
                ], 400);
            }

            $exists = Payroll::where('employee_id', $employee->id)
                ->where('period', $request->period)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll sudah ada untuk bulan ini'
                ], 400);
            }

            $payroll = $this->calculatePayroll(
                $employee,
                $request->period,
                $request->allowance ?? 0,
                $request->bonus ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => 'Payroll berhasil dibuat',
                'data' => $payroll
            ]);
        });
    }

    // 🔥 GENERATE BULK
    public function generateMonthly(Request $request)
    {
        $request->validate([
            'period' => 'required'
        ]);

        $employees = Employee::whereNotNull('salary')->get();

        $result = [];

        DB::beginTransaction();

        try {
            foreach ($employees as $employee) {

                $exists = Payroll::where('employee_id', $employee->id)
                    ->where('period', $request->period)
                    ->exists();

                if ($exists) continue;

                $payroll = $this->calculatePayroll($employee, $request->period);
                $result[] = $payroll;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Generate payroll berhasil',
                'total' => count($result),
                'data' => $result
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error generate payroll',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 📌 DETAIL
    public function show($id)
    {
        $data = Payroll::with(['employee', 'details'])->find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll tidak ditemukan',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil ambil detail payroll',
            'data' => $data
        ]);
    }

    // 📌 UPDATE
    public function update(Request $request, $id)
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll tidak ditemukan'
            ], 404);
        }

        if ($payroll->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa edit payroll'
            ], 400);
        }

        $payroll->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Berhasil update payroll',
            'data' => $payroll
        ]);
    }

    // 📌 DELETE
    public function destroy($id)
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll tidak ditemukan'
            ], 404);
        }

        $payroll->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deleted'
        ]);
    }

    // 🔥 APPROVE
    public function approve($id)
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll tidak ditemukan'
            ], 404);
        }

        $payroll->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Payroll approved',
            'data' => $payroll
        ]);
    }

    // 💸 PAY
    public function pay($id)
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll tidak ditemukan'
            ], 404);
        }

        $payroll->update(['status' => 'paid']);

        return response()->json([
            'success' => true,
            'message' => 'Payroll paid',
            'data' => $payroll
        ]);
    }

    // 🧠 CORE LOGIC
    private function calculatePayroll($employee, $period, $allowance = 0, $bonus = 0)
    {
        $gaji = $employee->salary;

        $bruto = $gaji + $allowance + $bonus;

        $bpjs_kesehatan = $gaji * 0.01;
        $bpjs_ketenagakerjaan = ($gaji * 0.02) + ($gaji * 0.01);

        $netto_tahun = ($bruto * 12) * 0.95;
        $ptkp = 54000000;
        $pkp = max(0, $netto_tahun - $ptkp);

        if ($pkp <= 60000000) {
            $pph21_tahun = $pkp * 0.05;
        } else {
            $pph21_tahun = (60000000 * 0.05) + (($pkp - 60000000) * 0.15);
        }

        $pph21 = $pph21_tahun / 12;

        $total_deduction = $bpjs_kesehatan + $bpjs_ketenagakerjaan + $pph21;
        $take_home_pay = $bruto - $total_deduction;

        return Payroll::create([
            'employee_id' => $employee->id,
            'period' => $period,
            'basic_salary' => $gaji,
            'allowance' => $allowance,
            'bonus' => $bonus,
            'bpjs_kesehatan' => $bpjs_kesehatan,
            'bpjs_ketenagakerjaan' => $bpjs_ketenagakerjaan,
            'pph21' => $pph21,
            'total_deduction' => $total_deduction,
            'take_home_pay' => $take_home_pay,
            'status' => 'draft'
        ]);
    }
}