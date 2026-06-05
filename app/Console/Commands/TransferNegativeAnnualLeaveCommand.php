<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Employee\Models\Employee;
use App\Modules\Leave\Services\AnnualLeaveService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransferNegativeAnnualLeaveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:transfer-negative';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer positive annual leave 3 balance to cover negative annual leave 2 balance.';

    /**
     * Execute the console command.
     */
    public function handle(AnnualLeaveService $annualLeaveService)
    {
        $this->info('Starting to fix negative annual leave balances...');

        // Find employees where AL2 is negative and AL3 is positive
        $employees = Employee::where('annual_leave_2', '<', 0)
            ->where('annual_leave_3', '>', 0)
            ->get();

        if ($employees->isEmpty()) {
            $this->info('No employees found needing balance adjustment.');
            return;
        }

        $count = 0;
        $date = Carbon::now();
        $currentYear = $date->year;
        $lastYear = $currentYear - 1;

        foreach ($employees as $employee) {
            DB::transaction(function () use ($employee, $annualLeaveService, $date, $currentYear, $lastYear) {
                $al2 = (float) $employee->annual_leave_2;
                $al3 = (float) $employee->annual_leave_3;

                $debt = abs($al2);
                $transferAmount = min($debt, $al3);

                $newAL2 = $al2 + $transferAmount;
                $newAL3 = $al3 - $transferAmount;

                $keterangan = "Sistem: Pemutihan minus cuti tahun sebelumnya ({$lastYear}) menggunakan sisa cuti tahun ini ({$currentYear}).";

                $annualLeaveService->adjustBalance($employee, $newAL2, $newAL3, $keterangan, $date);
            });

            $count++;
            $this->line("Fixed balances for employee ID: {$employee->id} (NIK: {$employee->employee_id_number})");
        }

        $this->info("Finished! Transferred balances for {$count} employees.");
    }
}
