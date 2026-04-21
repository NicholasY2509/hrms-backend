<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Models\EmployeeSalary;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Get the active salary configuration for an employee.
     * 
     * @param int $employeeId
     * @return EmployeeSalary|null
     */
    public function getActiveSalary(int $employeeId): ?EmployeeSalary
    {
        return EmployeeSalary::where('employee_id', $employeeId)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * Calculate hourly rate based on a given amount.
     * Based on standard regulation: Amount / 173.
     * 
     * @param float|null $amount
     * @return float
     */
    public function calculateRate(?float $amount): float
    {
        if (!$amount || $amount <= 0) {
            return 0;
        }

        return round($amount / 173, 2);
    }
}
