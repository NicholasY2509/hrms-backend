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
            ->with(['attendance_status', 'attendance_working_hour'])
            ->first();
    }
}
