<?php

namespace App\Modules\Payroll\Repositories;

use App\Modules\Payroll\Models\EmployeeSalaryComponent;
use Illuminate\Database\Eloquent\Collection;

class EmployeeSalaryComponentRepository
{
    public function getByEmployee(int $employeeId): Collection
    {
        return EmployeeSalaryComponent::with('component')
            ->where('employee_id', $employeeId)
            ->get();
    }

    public function updateOrCreate(int $employeeId, int $componentId, array $data): EmployeeSalaryComponent
    {
        return EmployeeSalaryComponent::updateOrCreate(
            ['employee_id' => $employeeId, 'salary_component_id' => $componentId],
            $data
        );
    }

    public function delete(int $id): bool
    {
        return EmployeeSalaryComponent::destroy($id) > 0;
    }
}
