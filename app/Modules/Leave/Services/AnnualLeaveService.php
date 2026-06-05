<?php

namespace App\Modules\Leave\Services;

use App\Modules\Employee\Models\Employee;
use App\Modules\Leave\Models\AnnualLeave;
use App\Modules\Leave\Repositories\AnnualLeaveRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnnualLeaveService
{
    public function __construct(
        protected AnnualLeaveRepository $repository
    ) {}

    /**
     * Add annual leave balance to an employee.
     *
     * @param Employee $employee
     * @param float $amount
     * @param string $keterangan
     * @param Carbon|null $date
     * @return AnnualLeave
     */
    public function add(Employee $employee, float $amount, string $keterangan, ?Carbon $date = null): AnnualLeave
    {
        $date = $date ?? Carbon::now();
        $currentYear = $date->year;
        $lastYear = $currentYear - 1;

        return DB::transaction(function () use ($employee, $amount, $keterangan, $date, $currentYear, $lastYear) {
            $balanceBefore = [
                $lastYear => (float) $employee->annual_leave_2,
                $currentYear => (float) $employee->annual_leave_3,
            ];

            $remaining = (float) $amount;
            $details = [];

            // 1. If annual_leave_2 (Last Year) is negative, pay it back first up to 0
            if ($remaining > 0 && $employee->annual_leave_2 < 0) {
                $payback = min($remaining, abs((float) $employee->annual_leave_2));
                $employee->annual_leave_2 += $payback;
                $remaining -= $payback;
                $details[$lastYear] = $payback;
            }

            // 2. Any remaining amount goes to annual_leave_3 (This Year)
            if ($remaining > 0) {
                $employee->annual_leave_3 += $remaining;
                $details[$currentYear] = ($details[$currentYear] ?? 0) + $remaining;
            }

            $employee->save();

            $balanceAfter = [
                $lastYear => (float) $employee->annual_leave_2,
                $currentYear => (float) $employee->annual_leave_3,
            ];

            return $this->repository->create([
                'employee_id' => $employee->id,
                'total' => $amount,
                'annual_leave_year' => $currentYear,
                'annual_leave_at' => $date,
                'status' => 'Tambah',
                'keterangan' => $keterangan,
                'deduction_details' => $details,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    /**
     * Deduct annual leave balance from employee buckets.
     *
     * Logic:
     * 1. Deduct from annual_leave_2 (last year) first, up to balance 0.
     * 2. If still needed, deduct from annual_leave_3 (this year) up to balance 0.
     * 3. If still needed, deduct from annual_leave_3 (this year) even if it goes negative.
     *
     * @param Employee $employee
     * @param float $amount
     * @param string $keterangan
     * @param Carbon|null $date
     * @return AnnualLeave
     */
    public function deduct(Employee $employee, float $amount, string $keterangan, ?Carbon $date = null): AnnualLeave
    {
        $date = $date ?? Carbon::now();
        $currentYear = $date->year;
        $lastYear = $currentYear - 1;

        $balanceBefore = [
            $lastYear => (float) $employee->annual_leave_2,
            $currentYear => (float) $employee->annual_leave_3,
        ];

        $remaining = (float) $amount;
        $details = [];

        // 1. Deduct from annual_leave_2 (Last Year) up to 0
        if ($remaining > 0 && $employee->annual_leave_2 > 0) {
            $deductAL2 = min($remaining, (float) $employee->annual_leave_2);
            $employee->annual_leave_2 -= $deductAL2;
            $remaining -= $deductAL2;
            $details[$lastYear] = $deductAL2;
        }

        // 2. Deduct from annual_leave_3 (This Year) up to 0
        if ($remaining > 0 && $employee->annual_leave_3 > 0) {
            $deductAL3 = min($remaining, (float) $employee->annual_leave_3);
            $employee->annual_leave_3 -= $deductAL3;
            $remaining -= $deductAL3;
            $details[$currentYear] = ($details[$currentYear] ?? 0) + $deductAL3;
        }

        // 3. Deduct from annual_leave_3 (This Year) - negative allowed
        if ($remaining > 0) {
            $employee->annual_leave_3 -= $remaining;
            $details[$currentYear] = ($details[$currentYear] ?? 0) + $remaining;
            $remaining = 0;
        }

        return DB::transaction(function () use ($employee, $amount, $keterangan, $date, $details, $currentYear, $lastYear, $balanceBefore) {
            $employee->save();

            $balanceAfter = [
                $lastYear => (float) $employee->annual_leave_2,
                $currentYear => (float) $employee->annual_leave_3,
            ];

            return $this->repository->create([
                'employee_id' => $employee->id,
                'total' => $amount,
                'annual_leave_year' => $currentYear,
                'annual_leave_at' => $date,
                'status' => 'Potong',
                'keterangan' => $keterangan,
                'deduction_details' => $details,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);
        });
    }

    /**
     * Restore annual leave balance from a previous deduction.
     * 
     * @param AnnualLeave $originalDeduction
     * @param string $reason
     * @return void
     */
    public function restoreDeduction(AnnualLeave $originalDeduction, string $reason): void
    {
        DB::transaction(function () use ($originalDeduction, $reason) {
            $employee = $originalDeduction->employee;
            $details = $originalDeduction->deduction_details;
            $currentYear = Carbon::now()->year;
            $lastYear = $currentYear - 1;

            $balanceBefore = [
                $lastYear => (float) $employee->annual_leave_2,
                $currentYear => (float) $employee->annual_leave_3,
            ];

            foreach ($details as $year => $amount) {
                if ((int)$year === $currentYear) {
                    $employee->annual_leave_3 += (float) $amount;
                } else {
                    // All other years (usually currentYear - 1) go to annual_leave_2
                    $employee->annual_leave_2 += (float) $amount;
                }
            }

            $employee->save();

            $balanceAfter = [
                $lastYear => (float) $employee->annual_leave_2,
                $currentYear => (float) $employee->annual_leave_3,
            ];

            // Create reversal history record
            $this->repository->create([
                'employee_id' => $employee->id,
                'total' => $originalDeduction->total,
                'annual_leave_year' => $currentYear,
                'annual_leave_at' => Carbon::now(),
                'status' => 'Tambah',
                'keterangan' => "Pengembalian: " . $reason . " (Ref: #" . $originalDeduction->id . ")",
                'deduction_details' => $details,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);

            // Soft delete the original deduction to avoid duplicate accounting
            // $originalDeduction->delete();
        });
    }

    /**
     * Adjust annual leave balance to exact amounts for an employee.
     * Records the addition or deduction of discarded amounts.
     *
     * @param Employee $employee
     * @param float $newAL2
     * @param float $newAL3
     * @param string $keterangan
     * @param Carbon|null $date
     * @return void
     */
    public function adjustBalance(Employee $employee, float $newAL2, float $newAL3, string $keterangan, ?Carbon $date = null): void
    {
        $date = $date ?: Carbon::now();
        $currentYear = $date->year;
        $lastYear = $currentYear - 1;

        DB::transaction(function () use ($employee, $newAL2, $newAL3, $keterangan, $date, $currentYear, $lastYear) {
            $balanceBefore = [
                $lastYear => (float) $employee->annual_leave_2,
                $currentYear => (float) $employee->annual_leave_3,
            ];

            $diffAL2 = $newAL2 - (float) $employee->annual_leave_2;
            $diffAL3 = $newAL3 - (float) $employee->annual_leave_3;

            $employee->annual_leave_2 = $newAL2;
            $employee->annual_leave_3 = $newAL3;
            $employee->save();

            $balanceAfter = [
                $lastYear => (float) $employee->annual_leave_2,
                $currentYear => (float) $employee->annual_leave_3,
            ];

            // Handle additions
            $additions = [];
            $totalAddition = 0;
            if ($diffAL2 > 0) {
                $additions[$lastYear] = $diffAL2;
                $totalAddition += $diffAL2;
            }
            if ($diffAL3 > 0) {
                $additions[$currentYear] = $diffAL3;
                $totalAddition += $diffAL3;
            }

            if ($totalAddition > 0) {
                $this->repository->create([
                    'employee_id' => $employee->id,
                    'total' => $totalAddition,
                    'annual_leave_year' => $currentYear,
                    'annual_leave_at' => $date,
                    'status' => 'Tambah',
                    'keterangan' => $keterangan,
                    'deduction_details' => $additions,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                ]);
            }

            // Handle deductions
            $deductions = [];
            $totalDeduction = 0;
            if ($diffAL2 < 0) {
                $deductions[$lastYear] = abs($diffAL2);
                $totalDeduction += abs($diffAL2);
            }
            if ($diffAL3 < 0) {
                $deductions[$currentYear] = abs($diffAL3);
                $totalDeduction += abs($diffAL3);
            }

            if ($totalDeduction > 0) {
                $this->repository->create([
                    'employee_id' => $employee->id,
                    'total' => $totalDeduction,
                    'annual_leave_year' => $currentYear,
                    'annual_leave_at' => $date,
                    'status' => 'Potong',
                    'keterangan' => $keterangan,
                    'deduction_details' => $deductions,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                ]);
            }
        });
    }

    /**
     * Create an annual leave record without affecting employee balances.
     *
     * @param array $data
     * @return AnnualLeave
     */
    public function recordOnly(array $data): AnnualLeave
    {
        $date = isset($data['annual_leave_at']) ? Carbon::parse($data['annual_leave_at']) : Carbon::now();
        $currentYear = $date->year;

        return $this->repository->create([
            'employee_id' => $data['employee_id'],
            'total' => $data['total'],
            'annual_leave_year' => $data['annual_leave_year'] ?? $currentYear,
            'annual_leave_at' => $date,
            'status' => $data['status'],
            'keterangan' => $data['keterangan'],
            'deduction_details' => $data['deduction_details'] ?? [],
            'balance_before' => $data['balance_before'] ?? null,
            'balance_after' => $data['balance_after'] ?? null,
        ]);
    }
}
