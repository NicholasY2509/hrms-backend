<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $overtimes = DB::table('overtimes')
            ->whereNull('estimated_overtime_price')
            ->orWhere('estimated_overtime_price', 0)
            ->get();

        foreach ($overtimes as $overtime) {
            // Get employee latest salary
            $salary = DB::table('employee_salaries')
                ->where('employee_id', $overtime->employee_id)
                ->orderByDesc('id')
                ->first();

            if (!$salary) continue;

            $salaryAmount = $salary->real_amount ?: $salary->amount;
            if (!$salaryAmount) continue;

            $start = Carbon::parse($overtime->start_time);
            $finish = Carbon::parse($overtime->finish_time);

            // Handle overnight overtime
            if ($finish->lessThan($start)) {
                $finish->addDay();
            }

            $totalHours = $start->diffInMinutes($finish) / 60;
            
            $effectiveHours = 0;
            if ($overtime->type === 'NATIONAL') {
                $effectiveHours = $totalHours;
            } elseif ($overtime->type === 'UMUM') {
                $effectiveHours = $totalHours > 1 ? $totalHours - 1 : 0;
            } else { // DAC
                $effectiveHours = $totalHours;
            }

            if ($effectiveHours <= 0) {
                DB::table('overtimes')->where('id', $overtime->id)->update([
                    'estimated_overtime_price' => 0
                ]);
                continue;
            }

            $hourlyRate = $salaryAmount / 173;
            $cost = 0;

            if ($overtime->type === 'NATIONAL') {
                if ($effectiveHours <= 7) {
                    $cost = $effectiveHours * 2 * $hourlyRate;
                } elseif ($effectiveHours <= 8) {
                    $cost = (7 * 2 * $hourlyRate) + (($effectiveHours - 7) * 3 * $hourlyRate);
                } else {
                    $cost = (7 * 2 * $hourlyRate) + (1 * 3 * $hourlyRate) + (($effectiveHours - 8) * 4 * $hourlyRate);
                }
            } elseif ($overtime->type === 'UMUM') {
                if ($effectiveHours <= 1) {
                    $cost = $effectiveHours * 1.5 * $hourlyRate;
                } else {
                    $cost = (1 * 1.5 * $hourlyRate) + (($effectiveHours - 1) * 2 * $hourlyRate);
                }
            } else { // DAC
                $cost = $effectiveHours * 2 * $hourlyRate;
            }

            DB::table('overtimes')->where('id', $overtime->id)->update([
                'estimated_overtime_price' => round($cost)
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse data population
    }
};
