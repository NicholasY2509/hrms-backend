<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Repositories\EmployeeSalaryRepository;
use App\Modules\Payroll\Models\EmployeeSalary;
use Illuminate\Support\Facades\DB;

class EmployeeSalaryService
{
    public function __construct(
        protected EmployeeSalaryRepository $repository
    ) {}

    public function getSalaryHistory(int $employeeId)
    {
        return $this->repository->getByEmployee($employeeId);
    }

    public function getActiveSalary(int $employeeId)
    {
        return $this->repository->findActive($employeeId);
    }

    public function updateBaseSalary(int $employeeId, array $data): EmployeeSalary
    {
        return DB::transaction(function () use ($employeeId, $data) {
            // Deactivate current active salary
            $this->repository->deactivateAll($employeeId);

            // Create new salary record
            return $this->repository->create(array_merge($data, [
                'employee_id' => $employeeId,
                'is_active' => true
            ]));
        });
    }
}
