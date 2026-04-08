<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    // 🔥 RESPONSE HELPER
    private function success($message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    private function error($message, $code = 400, $extra = [])
    {
        return response()->json(array_merge([
            'success' => false,
            'message' => $message
        ], $extra), $code);
    }

    // 📌 GET ALL
    public function index()
    {
        $data = Payroll::with(['employee', 'details'])->latest()->get();

        return $this->success(
            $data->isEmpty()
                ? 'Data payroll belum ada'
                : 'Berhasil ambil data payroll',
            $data
        );
    }

    // 📌 MY PAYROLL
    public function myPayroll(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->first();

        if (!$employee) {
            return $this->error('Employee tidak ditemukan', 404);
        }

        $data = Payroll::with('details')
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return $this->success(
            $data->isEmpty()
                ? 'Belum ada payroll'
                : 'Berhasil ambil payroll',
            $data
        );
    }

    // 📌 STORE
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required'
        ]);

        return DB::transaction(function () use ($request) {

            $employee = Employee::findOrFail($request->employee_id);

            if (!$employee->salary) {
                return $this->error('Salary employee belum di set', 400);
            }

            $exists = Payroll::where('employee_id', $employee->id)
                ->where('period', $request->period)
                ->exists();

            if ($exists) {
                return $this->error('Payroll sudah ada untuk bulan ini', 400);
            }

            $payroll = $this->calculatePayroll(
                $employee,
                $request->period,
                $request->allowance ?? 0,
                $request->bonus ?? 0
            );

            return $this->success('Payroll berhasil dibuat', $payroll, 201);
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

            return $this->success('Generate payroll berhasil', [
                'total' => count($result),
                'data' => $result
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->error('Error generate payroll', 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // 📌 DETAIL
    public function show($id)
    {
        $data = Payroll::with(['employee', 'details'])->find($id);

        if (!$data) {
            return $this->error('Payroll tidak ditemukan', 404);
        }

        return $this->success('Berhasil ambil detail payroll', $data);
    }

    // 📌 UPDATE
    public function update(Request $request, $id)
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return $this->error('Payroll tidak ditemukan', 404);
        }

        if ($payroll->status !== 'draft') {
            return $this->error('Tidak bisa edit payroll', 400);
        }

        $payroll->update($request->all());

        return $this->success('Berhasil update payroll', $payroll);
    }

    // 📌 DELETE
    public function destroy($id)
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return $this->error('Payroll tidak ditemukan', 404);
        }

        $payroll->delete();

        return $this->success('Deleted');
    }

    // 🔥 APPROVE
    public function approve($id)
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return $this->error('Payroll tidak ditemukan', 404);
        }

        if ($payroll->status !== 'draft') {
            return $this->error('Payroll sudah diproses', 400);
        }

        $payroll->update(['status' => 'approved']);

        return $this->success('Payroll approved', $payroll);
    }

    // 💸 PAY
    public function pay($id)
    {
        $payroll = Payroll::find($id);

        if (!$payroll) {
            return $this->error('Payroll tidak ditemukan', 404);
        }

        if ($payroll->status !== 'approved') {
            return $this->error('Payroll harus di-approve dulu', 400);
        }

        $payroll->update(['status' => 'paid']);

        return $this->success('Payroll paid', $payroll);
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