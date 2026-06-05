<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('leave:populate-balances')]
#[Description('Populate balance_before and balance_after on annual leaves based on current balances')]
class PopulateAnnualLeaveBalances extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to populate annual leave balances...');

        $employees = DB::table('employees')->whereNull('deleted_at')->get();

        $bar = $this->output->createProgressBar(count($employees));
        $bar->start();

        foreach ($employees as $employee) {
            $currentYear = date('Y');
            $lastYear = $currentYear - 1;
            
            // Current balance is assumed to be the final correct amount
            $currentBalance = [
                $lastYear => (float) $employee->annual_leave_2,
                $currentYear => (float) $employee->annual_leave_3,
            ];

            // Fetch active annual leaves for this employee, ordered from newest to oldest
            $leaves = DB::table('annual_leaves')
                ->where('employee_id', $employee->id)
                ->whereNull('deleted_at')
                ->where('created_at', '>=', '2026-01-01 00:00:00')
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            foreach ($leaves as $leave) {
                $balanceAfter = $currentBalance;
                $status = $leave->status;
                
                $details = json_decode($leave->deduction_details, true);
                if (!is_array($details) || empty($details)) {
                    $yearFallback = $leave->annual_leave_year ?? $currentYear;
                    $details = [
                        $yearFallback => (float) $leave->total
                    ];
                }

                $balanceBefore = $balanceAfter;

                // Back-calculate the balance before this transaction
                if ($status === 'Tambah') {
                    // Reverse Tambah: subtract the details
                    foreach ($details as $year => $amount) {
                        $balanceBefore[$year] = ($balanceBefore[$year] ?? 0) - (float) $amount;
                    }
                } elseif ($status === 'Log') {
                    // Log-only records don't affect balance
                    $balanceBefore = $balanceAfter;
                } else {
                    // Assume 'Potong' or null means deduction
                    // Reverse Potong: add the details back
                    foreach ($details as $year => $amount) {
                        $balanceBefore[$year] = ($balanceBefore[$year] ?? 0) + (float) $amount;
                    }
                }

                // Update the record without changing timestamps
                DB::table('annual_leaves')
                    ->where('id', $leave->id)
                    ->update([
                        'balance_before' => json_encode($balanceBefore),
                        'balance_after' => json_encode($balanceAfter),
                    ]);

                // The balance before this record becomes the balance after for the next (older) record
                $currentBalance = $balanceBefore;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Finished populating annual leave balances!');
    }
}
