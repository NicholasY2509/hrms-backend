<?php

namespace App\Modules\Employee\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Employee\Models\Employee;
use App\Modules\Attendance\Models\AttendanceWorkingHour;
use App\Modules\Attendance\Models\Attendance;
use Carbon\Carbon;

class DeleteResignedEmployeeAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:delete-resigned-attendances {--execute : Actually perform the deletions. Without this, it will only dry-run.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete attendance and schedule records that fall after an employee\'s resignation date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = !$this->option('execute');

        if ($isDryRun) {
            $this->warn('Running in DRY-RUN mode. No data will be deleted. Use --execute to apply changes.');
        } else {
            $this->info('Running in EXECUTE mode. Data will be deleted.');
        }

        // Find employees who are resigned
        $employees = Employee::where(function ($query) {
            $query->whereNotNull('resign_date')
                  ->orWhereIn('work_employee_status_id', [2, 3])
                  ->orWhereHas('resignations');
        })->with('resignations')->get();

        if ($employees->isEmpty()) {
            $this->info("No resigned employees found.");
            return 0;
        }

        $totalAttendancesDeleted = 0;
        $totalSchedulesDeleted = 0;

        foreach ($employees as $employee) {
            $resignationDate = $employee->resign_date;

            // If no resign_date directly on employee, try to get from resignations table
            if (!$resignationDate && $employee->resignations->isNotEmpty()) {
                $latestResignation = $employee->resignations->sortByDesc('effective_date')->first();
                $resignationDate = $latestResignation->effective_date;
            }

            if (!$resignationDate) {
                $this->warn("Skipped {$employee->full_name} (ID: {$employee->id}) - No effective resignation date found.");
                continue;
            }

            // Find all attendance working hours (schedules) AFTER the resignation date
            $workingHours = AttendanceWorkingHour::where('employee_id', $employee->id)
                ->where('attendance_at', '>', $resignationDate)
                ->get();

            $workingHourIds = $workingHours->pluck('id')->toArray();

            if (!empty($workingHourIds)) {
                // Find attendances linked to these working hours
                $attendancesCount = Attendance::whereIn('attendance_working_hour_id', $workingHourIds)->count();
                $schedulesCount = count($workingHourIds);

                $this->line("Found {$attendancesCount} attendances and {$schedulesCount} schedules to delete for {$employee->full_name} (Resigned on: {$resignationDate})");

                if (!$isDryRun) {
                    Attendance::whereIn('attendance_working_hour_id', $workingHourIds)->delete();
                    AttendanceWorkingHour::whereIn('id', $workingHourIds)->delete();
                }

                $totalAttendancesDeleted += $attendancesCount;
                $totalSchedulesDeleted += $schedulesCount;
            }
        }

        $this->info("--------------------------------------------------");
        if ($isDryRun) {
            $this->info("[DRY RUN] Total attendances that would be deleted: " . $totalAttendancesDeleted);
            $this->info("[DRY RUN] Total schedules that would be deleted: " . $totalSchedulesDeleted);
        } else {
            $this->info("Successfully deleted {$totalAttendancesDeleted} attendances and {$totalSchedulesDeleted} schedules.");
        }

        return 0;
    }
}
