<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Attendance\Events\EmployeeLateArrival;
use App\Modules\Notifications\Notifications\BaseNotification;
use Illuminate\Support\Facades\Log;

class AttendanceNotificationListener
{
    /**
     * Handle Employee Late Arrival event.
     */
    public function handleLateArrival(EmployeeLateArrival $event): void
    {
        $attendance = $event->attendance;
        $employee = $attendance->attendance_working_hour->employee;
        $supervisor = $employee->team?->team_head;

        // Notify Employee
        if ($employee->user) {
            $employee->user->notify(new BaseNotification([
                'title' => 'Terlambat Datang',
                'message' => "Anda tercatat terlambat datang pada hari ini selama {$attendance->late_time}.",
                'type' => 'attendance_late',
                'action_url' => '/portal/attendance'
            ]));
        }

        // Notify Supervisor
        if ($supervisor && $supervisor->user) {
            $supervisor->user->notify(new BaseNotification([
                'title' => 'Karyawan Terlambat',
                'message' => "{$employee->full_name} terlambat datang selama {$attendance->late_time}.",
                'type' => 'attendance_late_alert',
                'action_url' => "/management/attendance?employee_id={$employee->id}"
            ]));
        }
    }
}
