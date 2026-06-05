<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Leave\Models\AnnualLeave;

class FixMonthlyAccrualDeductionDetailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:fix-monthly-accruals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set deduction_details for Monthly Accrual (Automated) records in 2026';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Finding monthly accruals for 2026...');

        $accruals = AnnualLeave::where('status', 'Tambah')
            ->where('created_at', '>=', '2026-01-01 00:00:00')
            ->get()
            ->filter(function ($leave) {
                $date = \Carbon\Carbon::parse($leave->created_at);
                return $date->isLastOfMonth() && $date->hour === 0 && $date->minute === 0;
            });

        if ($accruals->isEmpty()) {
            $this->info('No monthly accrual records found.');
            return;
        }

        $count = 0;
        $bar = $this->output->createProgressBar(count($accruals));
        $bar->start();

        foreach ($accruals as $accrual) {
            $total = (float) $accrual->total;
            $accrual->deduction_details = [
                '2026' => $total,
            ];
            $accrual->save();
            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully updated deduction_details for {$count} records.");
    }
}
