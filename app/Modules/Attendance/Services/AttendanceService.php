<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\Attendance\Models\Attendance;

class AttendanceService
{
    protected AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * Get the attendance status for the authenticated user.
     *
     * @param int $userId
     * @return Attendance|null
     */
    public function getUserStatus(int $userId): ?Attendance
    {
        return $this->attendanceRepository->getStatusByUserId($userId);
    }
}
