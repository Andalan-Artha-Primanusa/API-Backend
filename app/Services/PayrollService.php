<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Calculate and generate payroll for a single employee
     *
     * @throws \DomainException
     */
    public function calculateAndCreate(Employee $employee, string $period, float $allowance = 0, float $bonus = 0): Payroll
    {
        $gaji = $employee->salary;

        if (!$gaji) {
            throw new \DomainException('Salary employee belum di set');
        }

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

    /**
     * Generate payrolls for all active employees for a given period
     * 
     * @throws \Exception
     */
    public function generateMonthlyBulk(string $period): array
    {
        $employees = Employee::whereNotNull('salary')->get();
        $result = [];

        DB::beginTransaction();

        try {
            foreach ($employees as $employee) {
                $exists = Payroll::where('employee_id', $employee->id)
                    ->where('period', $period)
                    ->exists();

                if ($exists) continue;

                $payroll = $this->calculateAndCreate($employee, $period);
                $result[] = $payroll;
            }

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
