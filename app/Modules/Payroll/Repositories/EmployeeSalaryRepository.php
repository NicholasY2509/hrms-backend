<?php

namespace App\Modules\Payroll\Repositories;

use App\Modules\Payroll\Models\EmployeeSalary;
use Illuminate\Database\Eloquent\Collection;

class EmployeeSalaryRepository
{
    public function getByEmployee(int $employeeId): Collection
    {
        return EmployeeSalary::where('employee_id', $employeeId)
            ->orderBy('effective_date', 'desc')
            ->get();
    }

    public function findActive(int $employeeId): ?EmployeeSalary
    {
        return EmployeeSalary::where('employee_id', $employeeId)
            ->where('is_active', true)
            ->first();
    }

    public function create(array $data): EmployeeSalary
    {
        return EmployeeSalary::create($data);
    }

    public function deactivateAll(int $employeeId): void
    {
        EmployeeSalary::where('employee_id', $employeeId)
            ->update(['is_active' => false]);
    }
}
