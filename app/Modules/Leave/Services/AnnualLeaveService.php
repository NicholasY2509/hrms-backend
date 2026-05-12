<?php

namespace App\Modules\Leave\Services;

use App\Modules\Employee\Models\Employee;
use App\Modules\Leave\Models\AnnualLeave;
use App\Modules\Leave\Repositories\AnnualLeaveRepository;
use Illuminate\Support\Carbon;
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
        $date = $date ?: Carbon::now();
        $currentYear = $date->year;

        return DB::transaction(function () use ($employee, $amount, $keterangan, $date, $currentYear) {
            $employee->annual_leave_3 += $amount;
            $employee->save();

            return $this->repository->create([
                'employee_id' => $employee->id,
                'total' => $amount,
                'annual_leave_year' => $currentYear,
                'annual_leave_at' => $date,
                'status' => 'Tambah',
                'keterangan' => $keterangan,
                'deduction_details' => [$currentYear => $amount],
            ]);
        });
    }

    /**
     * Deduct annual leave balance from employee buckets.
     *
     * Logic:
     * 1. Deduct from annual_leave_2 (last year) first, up to balance 0.
     * 2. If still needed, deduct from annual_leave_3 (this year) up to balance 0.
     * 3. If still needed, deduct from annual_leave_2 (last year) even if it goes negative.
     *
     * @param Employee $employee
     * @param float $amount
     * @param string $keterangan
     * @param Carbon|null $date
     * @return AnnualLeave
     */
    public function deduct(Employee $employee, float $amount, string $keterangan, ?Carbon $date = null): AnnualLeave
    {
        $date = $date ?: Carbon::now();
        $currentYear = $date->year;
        $lastYear = $currentYear - 1;
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

        // 3. Deduct from annual_leave_2 (Last Year) - negative allowed
        if ($remaining > 0) {
            $employee->annual_leave_2 -= $remaining;
            $details[$lastYear] = ($details[$lastYear] ?? 0) + $remaining;
            $remaining = 0;
        }

        return DB::transaction(function () use ($employee, $amount, $keterangan, $date, $details, $currentYear) {
            $employee->save();

            return $this->repository->create([
                'employee_id' => $employee->id,
                'total' => $amount,
                'annual_leave_year' => $currentYear,
                'annual_leave_at' => $date,
                'status' => 'Potong',
                'keterangan' => $keterangan,
                'deduction_details' => $details,
            ]);
        });
    }
}
