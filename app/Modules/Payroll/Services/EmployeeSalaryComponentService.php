<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Repositories\EmployeeSalaryComponentRepository;
use Illuminate\Database\Eloquent\Collection;

class EmployeeSalaryComponentService
{
    public function __construct(
        protected EmployeeSalaryComponentRepository $repository
    ) {}

    public function getEmployeeComponents(int $employeeId): Collection
    {
        return $this->repository->getByEmployee($employeeId);
    }

    public function assignComponent(int $employeeId, int $componentId, array $data)
    {
        return $this->repository->updateOrCreate($employeeId, $componentId, $data);
    }

    public function removeComponent(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
