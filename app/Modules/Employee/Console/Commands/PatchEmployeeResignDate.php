<?php

namespace App\Modules\Employee\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Models\Resignation;

class PatchEmployeeResignDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:patch-resign-date {default_date : The default date (Y-m-d) if no resignation record is found}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Patch missing resign_date for resigned employees using their resignation records or a default date.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $defaultDate = $this->argument('default_date');

        // Validate the date format
        if (!strtotime($defaultDate)) {
            $this->error("Invalid default date format. Please use Y-m-d.");
            return 1;
        }

        $this->info("Starting to patch missing resign dates...");

        // Find employees who are resigned (work_employee_status_id = 2) but have no resign_date
        $employees = Employee::where('work_employee_status_id', 2)
            ->whereNull('resign_date')
            ->get();

        if ($employees->isEmpty()) {
            $this->info("No resigned employees found with a missing resign_date.");
            return 0;
        }

        $patchedCount = 0;
        $defaultCount = 0;

        foreach ($employees as $employee) {
            // Find the latest resignation record for this employee
            $resignation = Resignation::where('employee_id', $employee->id)
                ->orderBy('effective_date', 'desc')
                ->first();

            if ($resignation && $resignation->effective_date) {
                $employee->resign_date = $resignation->effective_date;
                $this->line("Patched {$employee->full_name} (ID: {$employee->id}) with resignation effective date: {$resignation->effective_date}");
                $patchedCount++;
            } else {
                $employee->resign_date = $defaultDate;
                $this->line("Patched {$employee->full_name} (ID: {$employee->id}) with default date: {$defaultDate}");
                $defaultCount++;
            }

            $employee->save();
        }

        $this->info("Operation completed.");
        $this->info("Total employees patched: " . ($patchedCount + $defaultCount));
        $this->info("- From resignation records: $patchedCount");
        $this->info("- From default date: $defaultCount");

        return 0;
    }
}
