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
     * Get paginated attendances for management.
     */
    public function getPaginated(array $filters, int $perPage = 15)
    {
        $query = Attendance::query()
            ->with([
                'attendance_status',
                'attendance_working_hour.employee',
                'attendance_working_hour.working_hour',
                'incoming_location',
                'outgoing_location'
            ]);

        // Filter by Date Range (using attendance_working_hours.attendance_at)
        $query->whereHas('attendance_working_hour', function ($q) use ($filters) {
            if (!empty($filters['start_date'])) {
                $q->where('attendance_at', '>=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $q->where('attendance_at', '<=', $filters['end_date']);
            }
            if (!empty($filters['employee_id'])) {
                $q->where('employee_id', $filters['employee_id']);
            }
            
            if (!empty($filters['department_id'])) {
                $q->whereHas('employee', function ($eq) use ($filters) {
                    $eq->where('department_id', $filters['department_id']);
                });
            }

            if (!empty($filters['work_location_id'])) {
                $q->whereHas('employee', function ($eq) use ($filters) {
                    $eq->where('work_location_id', $filters['work_location_id']);
                });
            }
            
            // Search by employee name or NIK
            if (!empty($filters['search'])) {
                $q->whereHas('employee', function ($eq) use ($filters) {
                    $eq->where('full_name', 'like', '%' . $filters['search'] . '%')
                       ->orWhere('employee_id_number', 'like', '%' . $filters['search'] . '%');
                });
            }
        });

        if (!empty($filters['attendance_status_id'])) {
            $query->where('attendance_status_id', $filters['attendance_status_id']);
        }

        return $query->latest('id')->paginate($perPage);
    }

    /**
     * Get all attendance statuses.
     */
    public function getAllStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Modules\Attendance\Models\AttendanceStatus::all();
    }
}

