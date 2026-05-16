<?php

namespace App\Modules\Attendance\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Attendance\Models\AttendanceWorkingHour;
use App\Modules\Notifications\Notifications\BaseNotification;
use Carbon\Carbon;

class NotifyMissingAttendanceLogs extends Command
{
    protected $signature = 'attendance:notify-missing-logs';
    protected $description = 'Notify employees about missing attendance logs for yesterday';

    public function handle(): void
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        
        // Find working hours from yesterday that don't have an attendance record or have an incomplete one
        $incompleteSchedules = AttendanceWorkingHour::with(['employee.user', 'attendance'])
            ->where('attendance_at', $yesterday)
            ->whereHas('employee.user')
            ->where(function($query) {
                $query->whereDoesntHave('attendance')
                      ->orWhereHas('attendance', function($q) {
                          $q->whereNull('incoming_scan')->orWhereNull('outgoing_scan');
                      });
            })
            ->get();

        foreach ($incompleteSchedules as $schedule) {
            if ($schedule->employee && $schedule->employee->user) {
                $schedule->employee->user->notify(new BaseNotification([
                    'title' => 'Log Absensi Tidak Lengkap',
                    'message' => "Anda memiliki log absensi yang tidak lengkap untuk tanggal {$yesterday}. Harap segera lengkapi.",
                    'type' => 'attendance_missing_log',
                    'icon' => 'attendance_late',
                    'action_url' => '/portal/attendance'
                ]));
                $this->info("Notified {$schedule->employee->full_name} about missing log.");
            }
        }

        $this->info("Completed missing log notifications for {$incompleteSchedules->count()} employees.");
    }
}
