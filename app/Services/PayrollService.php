<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\OvertimeRequest;
use App\Enums\LeaveStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        // =========================
        // 🔥 OVERTIME CALCULATION
        // =========================
        $overtimePay = $this->calculateOvertimePay($employee, $period, $gaji);

        // =========================
        // 🏖️ PAID LEAVE CALCULATION
        // =========================
        $paidLeaveData = $this->calculatePaidLeave($employee, $period, $gaji);
        $paidLeaveDays = $paidLeaveData['days'];
        $paidLeaveAmount = $paidLeaveData['amount'];

        $bruto = $gaji + $allowance + $bonus + $overtimePay + $paidLeaveAmount;

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
            'overtime_pay' => $overtimePay,
            'paid_leave_days' => $paidLeaveDays,
            'paid_leave_amount' => $paidLeaveAmount,
            'bpjs_kesehatan' => $bpjs_kesehatan,
            'bpjs_ketenagakerjaan' => $bpjs_ketenagakerjaan,
            'pph21' => $pph21,
            'total_deduction' => $total_deduction,
            'take_home_pay' => $take_home_pay,
            'status' => 'draft'
        ]);
    }

    /**
     * Calculate overtime pay for an employee in a given period.
     */
    private function calculateOvertimePay(Employee $employee, string $period, float $monthlySalary): float
    {
        [$year, $month] = explode('-', $period);
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $approvedOvertimes = OvertimeRequest::with('overtimeRule')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        if ($approvedOvertimes->isEmpty()) {
            return 0;
        }

        $workingDaysPerMonth = 22;
        $hoursPerDay = 8;
        $hourlyRate = $monthlySalary / $workingDaysPerMonth / $hoursPerDay;

        $totalPay = 0;

        foreach ($approvedOvertimes as $ot) {
            $multiplier = $ot->overtimeRule?->multiplier ?? 1.5;
            $hours = $ot->overtime_minutes / 60;
            $totalPay += $hours * $hourlyRate * $multiplier;
        }

        return round($totalPay, 2);
    }

    /**
     * Calculate paid leave days and amount for an employee in a given period.
     *
     * @return array{days: float, amount: float}
     */
    private function calculatePaidLeave(Employee $employee, string $period, float $monthlySalary): array
    {
        [$year, $month] = explode('-', $period);
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $approvedLeaves = Leave::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('status', LeaveStatus::Approved)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            })
            ->get()
            ->filter(fn(Leave $leave) => $leave->leaveType?->is_paid);

        if ($approvedLeaves->isEmpty()) {
            return ['days' => 0, 'amount' => 0];
        }

        $periodStart = Carbon::parse($startDate);
        $periodEnd = Carbon::parse($endDate);
        $totalDays = 0;

        foreach ($approvedLeaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date);
            $leaveEnd = Carbon::parse($leave->end_date);
            $overlapStart = $leaveStart->max($periodStart);
            $overlapEnd = $leaveEnd->min($periodEnd);

            if ($overlapStart <= $overlapEnd) {
                $totalDays += $overlapStart->diffInDays($overlapEnd) + 1;
            }
        }

        if ($totalDays === 0) {
            return ['days' => 0, 'amount' => 0];
        }

        $workingDaysPerMonth = 22;
        $dailyRate = $monthlySalary / $workingDaysPerMonth;

        return [
            'days' => $totalDays,
            'amount' => round($dailyRate * $totalDays, 2),
        ];
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
