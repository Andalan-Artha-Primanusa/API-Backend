<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\TrainingProgram;
use App\Models\Competency;
use App\Models\EmployeeCompetency;
use App\Models\Asset;
use App\Models\AssetAssignment;

class ReportingService
{
    /**
     * Get comprehensive HR dashboard summary
     */
    public function getDashboardSummary(?array $filters = null)
    {
        $filters = $filters ?? [];

        return [
            'headcount' => $this->getHeadcountMetrics($filters),
            'attendance' => $this->getAttendanceSummary($filters),
            'leave' => $this->getLeaveSummary($filters),
            'payroll' => $this->getPayrollSummary($filters),
            'training' => $this->getTrainingSummary($filters),
            'assets' => $this->getAssetSummary($filters),
        ];
    }

    /**
     * Get detailed attendance analytics
     */
    public function getAttendanceAnalytics(?array $filters = null)
    {
        $filters = $filters ?? [];
        $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date']) : Carbon::now()->startOfMonth();
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date']) : Carbon::now()->endOfMonth();

        $attendanceData = Attendance::whereBetween('date', [$startDate, $endDate])
            ->with(['employee.user.profile'])
            ->get();

        $summary = [
            'total_working_days' => $this->calculateWorkingDays($startDate, $endDate),
            'present_count' => $attendanceData->where('status', 'present')->count(),
            'absent_count' => $attendanceData->where('status', 'absent')->count(),
            'late_count' => $attendanceData->where('status', 'late')->count(),
            'permission_count' => $attendanceData->where('status', 'permission')->count(),
        ];

        $summary['attendance_rate'] = $summary['total_working_days'] > 0
            ? round(($summary['present_count'] / ($summary['total_working_days'] * Employee::active()->count())) * 100, 2)
            : 0;

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => $summary,
            'by_employee' => $this->getAttendanceByEmployee($attendanceData),
            'trends' => $this->getAttendanceTrends($startDate, $endDate),
        ];
    }

    /**
     * Get leave utilization analytics
     */
    public function getLeaveAnalytics(?array $filters = null)
    {
        $filters = $filters ?? [];
        $year = isset($filters['year']) ? $filters['year'] : Carbon::now()->year;

        $leaves = Leave::where('status', 'approved')
            ->whereYear('start_date', $year)
            ->with(['employee.user.profile', 'leaveType'])
            ->get();

        $summary = [
            'total_leaves_taken' => $leaves->count(),
            'total_days_used' => $leaves->sum('duration'),
            'by_type' => $leaves->groupBy('leave_type_id')->map(function ($group) {
                return [
                    'type' => $group->first()->leaveType->name ?? 'Unknown',
                    'count' => $group->count(),
                    'days' => $group->sum('duration'),
                ];
            }),
        ];

        return [
            'year' => $year,
            'summary' => $summary,
            'by_employee' => $this->getLeaveByEmployee($leaves),
            'pending' => Leave::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get payroll analytics
     */
    public function getPayrollAnalytics(?array $filters = null)
    {
        $filters = $filters ?? [];
        $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date']) : Carbon::now()->startOfMonth();
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date']) : Carbon::now()->endOfMonth();

        $payrolls = Payroll::whereBetween('period', [$startDate, $endDate])
            ->with(['employee.user.profile', 'details'])
            ->get();

        $summary = [
            'total_payroll_count' => $payrolls->count(),
            'total_salary' => $payrolls->sum('basic_salary'),
            'total_allowance' => $payrolls->sum('allowance'),
            'total_deduction' => $payrolls->sum('deduction'),
            'total_bonus' => $payrolls->sum('bonus'),
            'total_net_pay' => $payrolls->sum('net_pay'),
            'average_salary' => $payrolls->count() > 0 ? round($payrolls->avg('net_pay'), 2) : 0,
        ];

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => $summary,
            'by_status' => $payrolls->groupBy('status')->map(function ($group) {
                return [
                    'status' => $group->first()->status,
                    'count' => $group->count(),
                    'total' => $group->sum('net_pay'),
                ];
            }),
            'by_employee_top_earners' => $payrolls->sortByDesc('net_pay')->take(10)->map(function ($p) {
                return [
                    'employee' => $p->employee->user->profile->full_name ?? $p->employee->user->name,
                    'net_pay' => $p->net_pay,
                    'status' => $p->status,
                ];
            })->values(),
        ];
    }

    /**
     * Get competency and training analytics
     */
    public function getCompetencyAnalytics(?array $filters = null)
    {
        $filters = $filters ?? [];

        $employeeCompetencies = EmployeeCompetency::with(['employee', 'competency'])
            ->get();

        $summary = [
            'total_assignments' => $employeeCompetencies->count(),
            'unique_employees' => $employeeCompetencies->groupBy('employee_id')->count(),
            'top_competencies' => $employeeCompetencies->groupBy('competency_id')
                ->map(function ($group) {
                    $comp = $group->first()->competency;
                    return ['name' => $comp->name ?? 'Unknown', 'count' => $group->count()];
                })
                ->sortByDesc('count')
                ->take(10)
                ->values(),
        ];

        $trainings = TrainingProgram::where('status', 'completed')
            ->with(['participants'])
            ->get();

        return [
            'competencies' => $summary,
            'training' => [
                'total_programs' => TrainingProgram::count(),
                'completed_trainings' => $trainings->count(),
                'total_participants' => $trainings->sum(function ($t) {
                    return $t->participants->count();
                }),
            ],
        ];
    }

    /**
     * Get employee lifecycle analytics
     */
    public function getEmployeeLifecycleAnalytics(?array $filters = null)
    {
        $filters = $filters ?? [];
        $year = isset($filters['year']) ? $filters['year'] : Carbon::now()->year;

        $employees = Employee::whereYear('created_at', $year)
            ->get();

        return [
            'year' => $year,
            'new_hires' => $employees->count(),
            'by_department' => $employees->groupBy('department')->map(function ($group) {
                return ['department' => $group->first()->department, 'count' => $group->count()];
            })->values(),
            'by_position' => $employees->groupBy('position')->map(function ($group) {
                return ['position' => $group->first()->position, 'count' => $group->count()];
            })->values(),
        ];
    }

    /**
     * Get asset management analytics
     */
    public function getAssetAnalytics(?array $filters = null)
    {
        $filters = $filters ?? [];

        $assets = Asset::with(['assignments'])->get();

        $assignments = AssetAssignment::where('status', 'active')->get();

        return [
            'total_assets' => $assets->count(),
            'assigned_assets' => $assignments->count(),
            'available_assets' => $assets->count() - $assignments->count(),
            'by_category' => $assets->groupBy('category')->map(function ($group) {
                return [
                    'category' => $group->first()->category,
                    'count' => $group->count(),
                    'assigned' => $group->flatMap('assignments')->where('status', 'active')->count(),
                ];
            })->values(),
        ];
    }

    /**
     * Helper: Get headcount metrics
     */
    private function getHeadcountMetrics(?array $filters = null): array
    {
        $employees = Employee::query();

        if (isset($filters['department'])) {
            $employees->where('department', $filters['department']);
        }

        $all = $employees->get();
        $active = $employees->where('status', 'active')->get();

        return [
            'total' => $all->count(),
            'active' => $active->count(),
            'inactive' => $all->count() - $active->count(),
            'by_department' => $all->groupBy('department')->map(fn($group) => $group->count())->toArray(),
        ];
    }

    /**
     * Helper: Get attendance summary
     */
    private function getAttendanceSummary(?array $filters = null): array
    {
        $today = Carbon::now();
        $attendance = Attendance::where('date', $today)->get();

        return [
            'present' => $attendance->where('status', 'present')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'permission' => $attendance->where('status', 'permission')->count(),
        ];
    }

    /**
     * Helper: Get leave summary
     */
    private function getLeaveSummary(?array $filters = null): array
    {
        $year = isset($filters['year']) ? $filters['year'] : Carbon::now()->year;

        return [
            'approved' => Leave::where('status', 'approved')->whereYear('start_date', $year)->count(),
            'pending' => Leave::where('status', 'pending')->count(),
            'rejected' => Leave::where('status', 'rejected')->whereYear('start_date', $year)->count(),
        ];
    }

    /**
     * Helper: Get payroll summary
     */
    private function getPayrollSummary(?array $filters = null): array
    {
        $month = isset($filters['month']) ? $filters['month'] : Carbon::now()->month;
        $year = isset($filters['year']) ? $filters['year'] : Carbon::now()->year;

        $payrolls = Payroll::whereMonth('period', $month)
            ->whereYear('period', $year)
            ->get();

        return [
            'total_amount' => $payrolls->sum('net_pay'),
            'approved' => $payrolls->where('status', 'approved')->count(),
            'paid' => $payrolls->where('status', 'paid')->count(),
            'pending' => $payrolls->where('status', 'draft')->count(),
        ];
    }

    /**
     * Helper: Get training summary
     */
    private function getTrainingSummary(?array $filters = null): array
    {
        return [
            'total_programs' => TrainingProgram::count(),
            'active' => TrainingProgram::where('status', 'active')->count(),
            'completed' => TrainingProgram::where('status', 'completed')->count(),
        ];
    }

    /**
     * Helper: Get asset summary
     */
    private function getAssetSummary(?array $filters = null): array
    {
        $assets = Asset::count();
        $assigned = AssetAssignment::where('status', 'active')->count();

        return [
            'total' => $assets,
            'assigned' => $assigned,
            'available' => $assets - $assigned,
        ];
    }

    /**
     * Helper: Calculate working days between dates
     */
    private function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        $count = 0;
        $current = $startDate->copy();

        while ($current <= $endDate) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($current->dayOfWeek !== 0 && $current->dayOfWeek !== 6) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Helper: Get attendance by employee
     */
    private function getAttendanceByEmployee($attendanceData): array
    {
        return $attendanceData->groupBy('employee_id')->map(function ($records) {
            $employee = $records->first()->employee;
            return [
                'employee_id' => $employee->id,
                'name' => $employee->user->profile->full_name ?? $employee->user->name,
                'present' => $records->where('status', 'present')->count(),
                'absent' => $records->where('status', 'absent')->count(),
                'late' => $records->where('status', 'late')->count(),
                'permission' => $records->where('status', 'permission')->count(),
            ];
        })->values()->toArray();
    }

    /**
     * Helper: Get attendance trends
     */
    private function getAttendanceTrends(Carbon $startDate, Carbon $endDate): array
    {
        $trends = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dayAttendance = Attendance::where('date', $current->toDateString())->get();
            $trends[] = [
                'date' => $current->format('Y-m-d'),
                'present' => $dayAttendance->where('status', 'present')->count(),
                'absent' => $dayAttendance->where('status', 'absent')->count(),
            ];
            $current->addDay();
        }

        return $trends;
    }

    /**
     * Helper: Get leave by employee
     */
    private function getLeaveByEmployee($leaves): array
    {
        return $leaves->groupBy('employee_id')->map(function ($records) {
            $employee = $records->first()->employee;
            return [
                'employee_id' => $employee->id,
                'name' => $employee->user->profile->full_name ?? $employee->user->name,
                'total_days' => $records->sum('duration'),
                'count' => $records->count(),
            ];
        })->values()->toArray();
    }
}
