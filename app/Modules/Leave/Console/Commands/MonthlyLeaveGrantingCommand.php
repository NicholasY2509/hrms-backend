<?php

namespace App\Modules\Leave\Console\Commands;

use App\Modules\Employee\Models\Employee;
use App\Modules\Leave\Services\AnnualLeaveService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonthlyLeaveGrantingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:grant-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant 1 day of annual leave balance to eligible tenured employees (>= 12 months)';

    /**
     * Execute the console command.
     */
    public function handle(AnnualLeaveService $service)
    {
        $this->info('Starting monthly leave granting process...');
        Log::info('MonthlyLeaveGrantingCommand: Process started.');

        $cutoffDate = Carbon::now()->subMonths(12);
        
        // Find active employees joined at least 12 months ago
        $employees = Employee::where('work_employee_status_id', 1) // Active status
            ->where('join_date', '<=', $cutoffDate)
            ->where('is_get_annual_leave', true)
            ->get();

        $count = 0;
        $total = $employees->count();
        $this->info("Found {$total} eligible employees.");

        foreach ($employees as $employee) {
            try {
                $service->add(
                    $employee, 
                    1, 
                    'Monthly Accrual (Automated)', 
                    Carbon::now()
                );
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to grant leave to Employee ID {$employee->id}: " . $e->getMessage());
                Log::error("MonthlyLeaveGrantingCommand: Failed for ID {$employee->id}. Error: " . $e->getMessage());
            }
        }

        $this->info("Successfully granted leave to {$count} employees.");
        Log::info("MonthlyLeaveGrantingCommand: Process completed. Total granted: {$count}");

        return Command::SUCCESS;
    }
}
