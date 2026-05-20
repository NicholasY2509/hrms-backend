<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\EmployeeTaxProfile;

class EmployeeTaxProfileRepository
{
    public function findByEmployee(int $employeeId): ?EmployeeTaxProfile
    {
        return EmployeeTaxProfile::where('employee_id', $employeeId)->first();
    }

    public function updateOrCreate(int $employeeId, array $data): EmployeeTaxProfile
    {
        return EmployeeTaxProfile::updateOrCreate(
            ['employee_id' => $employeeId],
            $data
        );
    }
}
