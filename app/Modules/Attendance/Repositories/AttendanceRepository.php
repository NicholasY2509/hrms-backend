<?php

namespace App\Modules\Attendance\Repositories;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceWorkingHour;
use App\Modules\Attendance\Models\ZktecoAttendance;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Models\UserEmployee;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use Carbon\Carbon;

class AttendanceRepository
{
    /**
     * Get the attendance status for a specific user and date.
     *
     * @param int $userId
     * @param string|null $date
     * @return Attendance|null
     */
    public function getStatusByUserId(int $userId, ?string $date = null): ?Attendance
    {
        $date = $date ?? Carbon::now()->format('Y-m-d');

        return Attendance::query()
            ->whereHas('attendance_working_hour', function ($query) use ($userId, $date) {
                $query->where('attendance_at', $date)
                    ->whereHas('employee.user_employee', function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
            })
            ->with(['attendance_status', 'attendance_working_hour.working_hour'])
            ->first();
    }

    /**
     * Get the working hour for a user on a specific date.
     */
    public function getWorkingHourByUserId(int $userId, string $date): ?AttendanceWorkingHour
    {
        return AttendanceWorkingHour::query()
            ->where('attendance_at', $date)
            ->whereHas('employee.user_employee', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['working_hour', 'employee'])
            ->first();
    }

    /**
     * Get valid attendance locations for a user.
     */
    public function getValidLocationsByUserId(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        $userEmployee = UserEmployee::where('user_id', $userId)->with('employee')->first();
        
        if (!$userEmployee || !$userEmployee->employee) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        $query = \App\Modules\Attendance\Models\AttendanceLocation::query();

        return $query->get();
    }

    /**
     * Get attendance by working hour id.
     */
    public function getAttendanceByWorkingHourId(int $workingHourId): ?Attendance
    {
        return Attendance::where('attendance_working_hour_id', $workingHourId)->first();
    }

    /**
     * Save/update attendance record.
     */
    public function save(Attendance $attendance): Attendance
    {
        $attendance->save();
        return $attendance;
    }
    /**
     * Get working hours for a user within a nearby window (Yesterday & Today).
     */
    public function getNearbyWorkingHours(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');

        return AttendanceWorkingHour::query()
            ->whereIn('attendance_at', [$yesterday, $today])
            ->whereHas('employee.user_employee', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['working_hour', 'attendance.attendance_status'])
            ->orderBy('attendance_at', 'asc')
            ->get();
    }

    /**
     * Get attendance history for a user within a date range.
     */
    public function getHistory(int $userId, string $startDate, string $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return Attendance::query()
            ->join('attendance_working_hours', 'attendances.attendance_working_hour_id', '=', 'attendance_working_hours.id')
            ->whereBetween('attendance_working_hours.attendance_at', [$startDate, $endDate])
            ->whereHas('attendance_working_hour.employee.user_employee', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['attendance_status', 'attendance_working_hour.working_hour'])
            ->orderBy('attendance_working_hours.attendance_at', 'desc')
            ->select('attendances.*')
            ->get();
    }

    /**
     * Get attendance summary for a user within a date range.
     */
    public function getSummary(int $userId, string $startDate, string $endDate): \Illuminate\Support\Collection
    {
        return Attendance::query()
            ->join('attendance_working_hours', 'attendances.attendance_working_hour_id', '=', 'attendance_working_hours.id')
            ->join('attendance_statuses', 'attendances.attendance_status_id', '=', 'attendance_statuses.id')
            ->whereBetween('attendance_working_hours.attendance_at', [$startDate, $endDate])
            ->whereHas('attendance_working_hour.employee.user_employee', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->select('attendance_statuses.name', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('attendance_statuses.name')
            ->get();
    }

    /**
     * Get query for export/reports.
     */
    public function getExportQuery(array $filters)
    {
        return Attendance::query()
            ->with([
                'attendance_status',
                'attendance_working_hour.employee.department',
                'attendance_working_hour.employee.position',
                'attendance_working_hour.employee.team',
                'attendance_working_hour.working_hour',
                'incoming_location',
                'outgoing_location'
            ])
            ->filter($filters);
    }

    /**
     * Get paginated attendances for management.
     */
    public function getPaginated(array $filters, int $perPage = 15)
    {
        return $this->getExportQuery($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Get all attendance statuses.
     */
    public function getAllStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Modules\Attendance\Models\AttendanceStatus::all();
    }

    /**
     * Get active employees with their attendance machine UIDs.
     */
    public function getEmployeesForCalculation()
    {
        return Employee::query()
            ->with(['attendance_users'])
            ->where('work_location_id', '!=', 3) // Exclude Suryaraya / Vendor
            ->whereNull('resign_date')
            ->get();
    }

    /**
     * Get machine attendance logs for a set of UIDs and date range.
     */
    public function getZktecoAttendancesInRange(array $uids, string $startDate, string $endDate)
    {
        return ZktecoAttendance::query()
            ->whereIn('uid', $uids)
            ->whereBetween('attendance_at', [$startDate, $endDate])
            ->with(['zkteco_machine'])
            ->get();
    }

    /**
     * Get earliest attendance dates for UIDs (used for registration mapping).
     */
    public function getEarliestAttendances(array $uids)
    {
        return ZktecoAttendance::query()
            ->whereIn('uid', $uids)
            ->selectRaw('uid, MIN(created_at) as min_created, MIN(updated_at) as min_updated')
            ->groupBy('uid')
            ->get()
            ->keyBy('uid');
    }

    /**
     * Get settled leave records for employees in date range.
     */
    public function getLeavesInRange(array $employeeIds, string $startDate, string $endDate)
    {
        return UnpaidLeave::query()
            ->whereIn('employee_id', $employeeIds)
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->whereNotNull('settled_at')
            ->get();
    }

    /**
     * Get scheduled working hours for employees in date range.
     */
    public function getWorkingHoursInRange(array $employeeIds, string $startDate, string $endDate)
    {
        return AttendanceWorkingHour::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('attendance_at', [$startDate, $endDate])
            ->with(['working_hour'])
            ->get();
    }

    /**
     * Get existing attendance records for specific working hour IDs.
     */
    public function getAttendancesByWorkingHourIds(array $workingHourIds)
    {
        return Attendance::query()
            ->whereIn('attendance_working_hour_id', $workingHourIds)
            ->get();
    }

    /**
     * Find an attendance by ID.
     */
    public function findById(int $id): ?Attendance
    {
        return Attendance::with([
            'attendance_status',
            'attendance_working_hour.employee',
            'attendance_working_hour.working_hour',
            'incoming_location',
            'outgoing_location',
        ])->find($id);
    }
}

