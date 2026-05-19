<?php

namespace App\Modules\System\Services;

use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Attendance\Services\MobileAttendanceService;
use App\Modules\Employee\Services\EmployeeService;
use App\Modules\Overtime\Services\OvertimeService;
use App\Modules\UnpaidLeave\Services\UnpaidLeaveService;
use Carbon\Carbon;

class MobileDashboardService
{
    protected AttendanceService $attendanceService;
    protected UnpaidLeaveService $unpaidLeaveService;
    protected EmployeeService $employeeService;
    protected OvertimeService $overtimeService;
    protected MobileAttendanceService $mobileAttendanceService;

    public function __construct(
        MobileAttendanceService $mobileAttendanceService,
        AttendanceService $attendanceService,
        UnpaidLeaveService $unpaidLeaveService,
        EmployeeService $employeeService,
        OvertimeService $overtimeService
    ) {
        $this->mobileAttendanceService = $mobileAttendanceService;
        $this->attendanceService = $attendanceService;
        $this->unpaidLeaveService = $unpaidLeaveService;
        $this->employeeService = $employeeService;
        $this->overtimeService = $overtimeService;
    }

    /**
     * Get aggregated data for the employee mobile dashboard.
     *
     * @param int $userId
     * @return array
     */
    public function getDashboardData(int $userId): array
    {
        $employee = $this->employeeService->getProfile($userId);
        $employeeId = $employee?->id;

        $attendance = $this->mobileAttendanceService->getUserStatus($userId);
        $leaveRequests = $employeeId ? $this->unpaidLeaveService->getPendingRequests($employeeId) : collect();
        $overtimeRequests = $employeeId ? $this->overtimeService->getPendingRequests($employeeId) : collect();
        $holidays = $this->unpaidLeaveService->getUpcomingHolidays(2);

        // Merge and prepare pending requests
        $pendingRequests = collect();
        
        // Add leaves
        foreach ($leaveRequests as $leave) {
            $pendingRequests->push([
                'id' => $leave->id,
                'type' => 'leave',
                'title' => $leave->unpaid_leave_type?->name ?? 'Unpaid Leave',
                'date_info' => $leave->start_date . ($leave->start_date != $leave->end_date ? ' to ' . $leave->end_date : ''),
                'status' => $leave->status,
                'note' => $leave->note,
                'created_at' => $leave->created_at?->toDateTimeString(),
            ]);
        }

        // Add overtimes
        foreach ($overtimeRequests as $overtime) {
            $pendingRequests->push([
                'id' => $overtime->id,
                'type' => 'overtime',
                'title' => $overtime->type,
                'date_info' => $overtime->date,
                'status' => $overtime->status,
                'note' => $overtime->note,
                'created_at' => $overtime->created_at?->toDateTimeString(),
            ]);
        }

        $pendingRequests = $pendingRequests->sortByDesc('created_at')->values();

        // Fetch monthly attendance summary
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $today = $now->toDateString();
        $daysInMonthToDate = $now->day;

        $summary = $this->mobileAttendanceService->getSummary($userId, $startOfMonth, $today);
        
        $hadirCount = 0;
        $liburCount = 0;
        $terlambatCount = 0;
        $otherPresentCount = 0;

        foreach ($summary as $item) {
            if ($item->name === 'Hadir') $hadirCount = $item->count;
            if ($item->name === 'Libur') $liburCount = $item->count;
            if ($item->name === 'Terlambat') $terlambatCount = $item->count;
            if (in_array($item->name, ['Izin', 'Cuti', 'Sakit', 'Dinas Luar'])) $otherPresentCount += $item->count;
        }

        $totalData = $summary->sum('count');
        $attendanceRate = ($totalData > 0) ? (($hadirCount + $liburCount) / $totalData) * 100 : 0;

        $attendanceSummary = $summary->map(function ($item) use ($totalData, $liburCount, $terlambatCount) {
            $count = $item->count;
            
            if ($item->name === 'Hadir') {
                $count += $liburCount + $terlambatCount;
            }

            $percentage = ($totalData > 0) ? ($count / $totalData) * 100 : 0;
            
            return [
                'name' => $item->name,
                'count' => $item->count,
                'percentage' => round($percentage, 1),
            ];
        });

        return [
            'employee' => $employee,
            'attendance' => $attendance,
            'pending_requests' => $pendingRequests,
            'holidays' => $holidays,
            'tenure' => $this->calculateTenure($employee?->join_date),
            'attendance_summary' => $attendanceSummary,
            'attendance_rate' => round($attendanceRate, 1),
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
