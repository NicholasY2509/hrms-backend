<?php

namespace App\Modules\Attendance\Repositories;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceWorkingHour;
use App\Modules\Employee\Models\UserEmployee;
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

        $employee = $userEmployee->employee;

        // Legacy logic for department 7
        $workLocationId = $employee->department_id == 7 ? $employee->work_location_id : false;

        $query = \App\Modules\Attendance\Models\AttendanceLocation::query();
        
        if ($workLocationId) {
            $query->where('work_location_id', $workLocationId);
        }

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
}

