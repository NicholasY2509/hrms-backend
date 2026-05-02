<?php

namespace App\Modules\Leave\Services;

use App\Modules\Employee\Models\Employee;
use Illuminate\Support\Carbon;

class AnnualLeaveService
{
    /**
     * Deduct annual leave balance from employee buckets.
     * 
     * @param Employee $employee
     * @param float|int $amount
     * @param Carbon $referenceDate
     * @return array
     */
    public function deduct(Employee $employee, $amount, Carbon $referenceDate): array
    {
        $deduction_details = [];
        $currentYear = (int) $referenceDate->year;
        $remainingLeave = (float) $amount;

        // Bucket L2 (Last Year)
        // Only deduct from L2 if it has a positive balance.
        if ($remainingLeave > 0 && $employee->annual_leave_2 > 0) {
            $canDeduct = min($remainingLeave, (float)$employee->annual_leave_2);
            $employee->annual_leave_2 -= $canDeduct;
            $deduction_details[$currentYear - 1] = $canDeduct;
            $remainingLeave -= $canDeduct;
        }

        // Bucket L3 (Current Year)
        // Takes the remaining amount, even if L3 doesn't have enough balance (it can go negative).
        if ($remainingLeave > 0) {
            $employee->annual_leave_3 -= $remainingLeave;
            $deduction_details[$currentYear] = ($deduction_details[$currentYear] ?? 0) + $remainingLeave;
            $remainingLeave = 0;
        }

        return [
            'employee' => $employee,
            'deduction_details' => $deduction_details
        ];
    }
}
