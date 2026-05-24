<?php

namespace App\Modules\Attendance\Console\Commands;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceSetting;
use App\Modules\Attendance\Models\ZktecoMachine;
use App\Modules\Attendance\Services\AttendanceCalculationService;
use App\Modules\Attendance\Services\ZktecoLogService;
use App\Modules\Leave\Services\AnnualLeaveService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyAbsencePenaltyCommand extends Command
{
    use \App\Traits\TracksCommandTask;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:daily-absence-penalty {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch machine data, calculate attendance, and automatically deduct leave for absent employees';

    /**
     * Execute the console command.
     */
    public function handle(
        ZktecoLogService $logService,
        AttendanceCalculationService $calcService,
        AnnualLeaveService $leaveService
    ) {
        $targetDate = $this->argument('date') ?: Carbon::yesterday('Asia/Jakarta')->format('Y-m-d');
        
        $this->info("Starting Daily Absence Penalty Process for: {$targetDate}");
        Log::info("DailyAbsencePenaltyCommand: Started for {$targetDate}");

        // --- STEP 1: SYNC LOGS FROM MACHINES ---
        $machines = ZktecoMachine::all();
        $this->info("Syncing logs from " . $machines->count() . " active machines...");
        
        foreach ($machines as $machine) {
            try {
                $this->info("  Processing machine: {$machine->name}...");
                $result = $logService->syncLogs($machine, $targetDate, $targetDate);
                $this->info("    Fetched {$result['upserted']} logs.");
            } catch (\Exception $e) {
                $this->error("    Failed to sync from machine {$machine->name}: " . $e->getMessage());
                Log::error("DailyAbsencePenaltyCommand: Sync failed for {$machine->name}. Error: " . $e->getMessage());
            }
        }

        // --- STEP 2: CALCULATE ATTENDANCE ---
        $this->info("Recalculating attendance for {$targetDate}...");
        try {
            // Run calculation synchronously
            $calcService->calculate($targetDate, $targetDate);
            $this->info("Attendance calculation completed.");
        } catch (\Exception $e) {
            $this->error("Attendance calculation failed: " . $e->getMessage());
            Log::error("DailyAbsencePenaltyCommand: Calculation failed. Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        // --- STEP 3: DEDUCT LEAVE FOR ALPHA STATUS ---
        $alphaStatusId = AttendanceSetting::getValue('attendance_status_alpha_id', 2);
        $excludedPositions = [62, 63]; // Matching legacy exclusion

        $absences = Attendance::with(['attendance_working_hour.employee'])
            ->where('attendance_status_id', $alphaStatusId)
            ->whereHas('attendance_working_hour', function ($query) use ($targetDate) {
                $query->where('attendance_at', $targetDate);
            })
            ->whereHas('attendance_working_hour.employee', function ($q) use ($excludedPositions) {
                $q->whereNotIn('work_position_id', $excludedPositions);
            })
            ->get();

        $this->info("Found " . $absences->count() . " eligible absences for deduction.");
        
        $count = 0;
        foreach ($absences as $attendance) {
            try {
                $employee = $attendance->attendance_working_hour->employee;
                if ($employee) {
                    $leaveService->deduct(
                        $employee, 
                        1, 
                        "Potong Otomatis: Tidak Absen pada {$targetDate}", 
                        Carbon::parse($targetDate)
                    );
                    $count++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to deduct leave for Employee ID {$attendance->attendance_working_hour->employee_id}: " . $e->getMessage());
                Log::error("DailyAbsencePenaltyCommand: Deduction failed for ID {$attendance->attendance_working_hour->employee_id}. Error: " . $e->getMessage());
            }
        }

        $this->info("Process completed. Total absences penalized: {$count}");
        Log::info("DailyAbsencePenaltyCommand: Completed. Total penalized: {$count}");

        return Command::SUCCESS;
    }
}
