<?php

namespace App\Modules\System\Services;

use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Employee\Services\EmployeeService;
use App\Modules\UnpaidLeave\Services\UnpaidLeaveService;
use Carbon\Carbon;

class DashboardService
{
    protected AttendanceService $attendanceService;
    protected UnpaidLeaveService $unpaidLeaveService;
    protected EmployeeService $employeeService;

    public function __construct(
        AttendanceService $attendanceService,
        UnpaidLeaveService $unpaidLeaveService,
        EmployeeService $employeeService
    ) {
        $this->attendanceService = $attendanceService;
        $this->unpaidLeaveService = $unpaidLeaveService;
        $this->employeeService = $employeeService;
    }

    /**
     * Get aggregated data for the employee dashboard.
     *
     * @param int $userId
     * @return array
     */
    public function getDashboardData(int $userId): array
    {
        $employee = $this->employeeService->getProfile($userId);
        $employeeId = $employee?->id;

        $attendance = $this->attendanceService->getUserStatus($userId);
        $leaveSummary = $employeeId ? $this->unpaidLeaveService->getDashboardSummary($employeeId) : ['pending_count' => 0];
        $holidays = $this->unpaidLeaveService->getUpcomingHolidays(2);

        // Fetch monthly attendance summary
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $today = Carbon::now()->toDateString();
        $attendanceSummary = $this->attendanceService->getSummary($userId, $startOfMonth, $today);

        return [
            'employee' => $employee,
            'attendance' => $attendance,
            'leave' => $leaveSummary,
            'holidays' => $holidays,
            'tenure' => $this->calculateTenure($employee?->join_date),
            'attendance_summary' => $attendanceSummary,
        ];
    }

    /**
     * Calculate employee tenure.
     */
    protected function calculateTenure(?string $joinDate): ?string
    {
        if (!$joinDate) return null;

        $join = Carbon::parse($joinDate);
        $now = Carbon::now();

        $diff = $join->diff($now);

        if ($diff->y > 0) {
            return "{$diff->y} years " . ($diff->m > 0 ? "and {$diff->m} months" : "");
        }

        return "{$diff->m} months";
    }
}
