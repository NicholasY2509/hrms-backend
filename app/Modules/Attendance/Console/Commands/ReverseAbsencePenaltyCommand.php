<?php

namespace App\Modules\Attendance\Console\Commands;

use App\Modules\Leave\Models\AnnualLeave;
use App\Modules\Leave\Services\AnnualLeaveService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReverseAbsencePenaltyCommand extends Command
{
    use \App\Traits\TracksCommandTask;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:reverse-absence-penalty {date? : The target date that was penalized (e.g. 2026-05-24)} {--force : Force run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reverse the automatic daily absence penalty deductions for a specific date';

    /**
     * Execute the console command.
     */
    public function handle(AnnualLeaveService $leaveService)
    {
        $targetDate = $this->argument('date') ?: Carbon::yesterday('Asia/Jakarta')->format('Y-m-d');
        
        $this->info("Starting Reversal Process for Target Date: {$targetDate}");
        Log::info("ReverseAbsencePenaltyCommand: Started for target date {$targetDate}");

        // Find deductions that match this target date.
        // Recent records use "Potong Otomatis: Tidak Absen pada YYYY-MM-DD"
        $deductions = AnnualLeave::with('employee')
            ->where('status', 'Potong')
            ->where(function ($q) use ($targetDate) {
                $q->where('keterangan', 'like', "%Tidak Absen pada {$targetDate}%")
                  ->orWhere('keterangan', "Potong Otomatis: Tidak Absen pada {$targetDate}");
            })
            ->get();

        // If no matches found with the specific date string, attempt to fallback to "Tidak Absen" created on that day or the day after
        if ($deductions->isEmpty()) {
            $runDate = Carbon::parse($targetDate)->addDay()->format('Y-m-d');
            $this->warn("No specific keterangan match found for {$targetDate}. Falling back to 'Tidak Absen' created on {$targetDate} or {$runDate}...");
            $deductions = AnnualLeave::with('employee')
                ->where('status', 'Potong')
                ->where('keterangan', 'Tidak Absen')
                ->whereIn(DB::raw('DATE(created_at)'), [$targetDate, $runDate])
                ->get();
        }

        $this->info("Found " . $deductions->count() . " eligible deductions to reverse.");
        
        if ($deductions->isEmpty()) {
            $this->info("Nothing to reverse. Exiting.");
            return Command::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Do you wish to continue and reverse these {$deductions->count()} deductions?")) {
            $this->info("Operation cancelled.");
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($deductions as $deduction) {
            try {
                $employeeId = $deduction->employee_id;
                $leaveService->restoreDeduction($deduction, "Kesalahan sistem absen libur pada {$targetDate}");
                $this->line("Reversed deduction ID {$deduction->id} for Employee ID {$employeeId}.");
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to reverse deduction ID {$deduction->id}: " . $e->getMessage());
                Log::error("ReverseAbsencePenaltyCommand: Reversal failed for deduction ID {$deduction->id}. Error: " . $e->getMessage());
            }
        }

        $this->info("Process completed. Total deductions reversed: {$count}");
        Log::info("ReverseAbsencePenaltyCommand: Completed. Total reversed: {$count}");

        return Command::SUCCESS;
    }
}
