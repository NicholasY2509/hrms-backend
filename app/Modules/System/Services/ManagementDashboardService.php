<?php

namespace App\Modules\System\Services;

use App\Modules\Employee\Models\Employee;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceStatus;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\WorkLocation;
use App\Modules\Organization\Models\WorkPosition;
use App\Modules\Payroll\Models\EmployeeSalary;
use App\Modules\Employee\Models\Resignation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManagementDashboardService
{
    /**
     * Get aggregated data for the management dashboard.
     *
     * @return array
     */
    public function getDashboardData(): array
    {
        $today = Carbon::today()->toDateString();
        $sixMonthsAgo = Carbon::today()->subMonths(6)->startOfMonth();

        return [
            'workforce_overview' => $this->getWorkforceOverview(),
            'attendance_productivity' => $this->getAttendanceProductivity($today),
            'attrition_retention' => $this->getAttritionStats($sixMonthsAgo),
            'payroll_insights' => $this->getPayrollInsights(),
            'pending_requests_count' => $this->getPendingRequestsCount(),
        ];
    }

    /**
     * Workforce Overview: Headcount distribution and growth.
     */
    protected function getWorkforceOverview(): array
    {
        $activeEmployees = Employee::where('work_employee_status_id', 1);
        
        $total = Employee::count();
        $active = (clone $activeEmployees)->count();

        // Department distribution
        $byDepartment = Department::withCount(['employees' => function($q) {
            $q->where('work_employee_status_id', 1);
        }])->get()->map(fn($d) => ['name' => $d->name, 'count' => $d->employees_count]);

        // Location distribution
        $byLocation = WorkLocation::withCount(['employees' => function($q) {
            $q->where('work_employee_status_id', 1);
        }])->get()->map(fn($l) => ['name' => $l->name, 'count' => $l->employees_count]);

        // Gender distribution
        $genderStats = (clone $activeEmployees)
            ->select('gender_id', DB::raw('count(*) as count'))
            ->groupBy('gender_id')
            ->get()
            ->map(fn($g) => [
                'label' => $g->gender_id == 1 ? 'Male' : ($g->gender_id == 2 ? 'Female' : 'Unknown'),
                'count' => $g->count
            ]);

        // Growth (last 6 months)
        $growth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth()->toDateString();
            $end = $month->copy()->endOfMonth()->toDateString();
            
            $count = Employee::where('work_employee_status_id', 1)
                ->where('join_date', '<=', $end)
                ->count();

            $growth[] = [
                'month' => $month->format('M Y'),
                'count' => $count
            ];
        }

        return [
            'headcount' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $total - $active,
            ],
            'distribution' => [
                'department' => $byDepartment,
                'location' => $byLocation,
                'gender' => $genderStats,
            ],
            'growth_trend' => $growth
        ];
    }

    /**
     * Attendance & Productivity: Today's stats and OT.
     */
    protected function getAttendanceProductivity(string $date): array
    {
        $totalActive = Employee::where('work_employee_status_id', 1)->count();
        
        $stats = DB::table('attendances')
            ->join('attendance_statuses', 'attendances.attendance_status_id', '=', 'attendance_statuses.id')
            ->join('attendance_working_hours', 'attendances.attendance_working_hour_id', '=', 'attendance_working_hours.id')
            ->whereDate('attendance_working_hours.attendance_at', $date)
            ->select('attendance_statuses.name', DB::raw('count(*) as count'))
            ->groupBy('attendance_statuses.name')
            ->get();

        $presentCount = 0;
        $lateCount = 0;
        $onLeaveCount = 0;

        foreach ($stats as $stat) {
            if ($stat->name === 'Hadir') $presentCount = $stat->count;
            if ($stat->name === 'Terlambat') $lateCount = $stat->count;
            if (in_array($stat->name, ['Izin', 'Cuti', 'Sakit'])) $onLeaveCount += $stat->count;
        }

        $absentCount = max(0, $totalActive - ($presentCount + $lateCount + $onLeaveCount));
        $attendanceRate = $totalActive > 0 ? round((($presentCount + $lateCount) / $totalActive) * 100, 1) : 0;

        // Overtime this month
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $totalOtHours = DB::table('overtimes')
            ->where('date', '>=', $startOfMonth)
            ->whereNotNull('settled_at')
            ->sum('total_time');

        return [
            'today' => [
                'present' => $presentCount,
                'late' => $lateCount,
                'absent' => $absentCount,
                'on_leave' => $onLeaveCount,
                'attendance_rate' => $attendanceRate,
            ],
            'monthly_overtime_hours' => round($totalOtHours, 1)
        ];
    }

    /**
     * Attrition & Retention: Resignations and Turnover.
     */
    protected function getAttritionStats(Carbon $since): array
    {
        $resignations = Resignation::where('effective_date', '>=', $since->toDateString())
            ->whereNotNull('settled_at')
            ->get();

        $totalResigned = $resignations->count();
        
        // Turnover rate calculation (simplified: resigned / avg headcount)
        $currentHeadcount = Employee::where('work_employee_status_id', 1)->count();
        $turnoverRate = $currentHeadcount > 0 ? round(($totalResigned / $currentHeadcount) * 100, 1) : 0;

        $reasons = $resignations->groupBy('reason')->map(fn($group, $key) => [
            'reason' => $key ?: 'Not Specified',
            'count' => $group->count()
        ])->values();

        return [
            'total_resigned_6_months' => $totalResigned,
            'turnover_rate_period' => $turnoverRate,
            'reasons_distribution' => $reasons
        ];
    }

    /**
     * Payroll Insights: Total costs.
     */
    protected function getPayrollInsights(): array
    {
        $totalPayroll = EmployeeSalary::sum('amount');
        
        $byDept = DB::table('employee_salaries')
            ->join('employees', 'employee_salaries.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->select('departments.name', DB::raw('sum(employee_salaries.amount) as total'))
            ->groupBy('departments.name')
            ->get();

        return [
            'total_monthly_payroll' => round($totalPayroll, 2),
            'department_cost_breakdown' => $byDept
        ];
    }

    /**
     * Pending Requests: Summary from approval workflow.
     */
    protected function getPendingRequestsCount(): array
    {
        $stats = DB::table('approval_requests')
            ->where('status', 'pending')
            ->select('approvable_type', DB::raw('count(*) as count'))
            ->groupBy('approvable_type')
            ->get();

        $leaveCount = 0;
        $overtimeCount = 0;

        foreach ($stats as $stat) {
            if (str_contains($stat->approvable_type, 'UnpaidLeave')) $leaveCount += $stat->count;
            if (str_contains($stat->approvable_type, 'Overtime')) $overtimeCount += $stat->count;
        }

        return [
            'leave' => $leaveCount,
            'overtime' => $overtimeCount,
            'total' => $leaveCount + $overtimeCount,
        ];
    }
}
