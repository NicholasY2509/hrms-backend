<?php

namespace App\Modules\Payroll\Repositories;

use App\Modules\Employee\Models\Employee;
use App\Modules\Payroll\Models\EmployeeSalary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
            ->latest('created_at')
            ->first();
    }

    public function getAllLatestSalariesPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $paginator = Employee::query()
            ->where('work_employee_status_id', 1) // 1 = Active
            ->filter(['search' => $search])
            ->with('activeSalary')
            ->orderBy('first_name')
            ->paginate($perPage);

        $paginator->getCollection()->transform(function ($employee) {
            if ($employee->activeSalary) {
                $salary = $employee->activeSalary;
                $salary->setRelation('employee', $employee);
                return $salary;
            }

            $salary = new EmployeeSalary([
                'employee_id' => $employee->id,
                'bpjs_base_amount' => 0,
                'actual_base_amount' => 0,
                'is_active' => false,
            ]);
            $salary->id = 0;
            $salary->setRelation('employee', $employee);
            
            return $salary;
        });

        return $paginator;
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

    public function findById(int $id): ?EmployeeSalary
    {
        return EmployeeSalary::find($id);
    }

    public function update(int $id, array $data): ?EmployeeSalary
    {
        $salary = $this->findById($id);
        
        if ($salary) {
            $salary->update($data);
            return $salary->fresh();
        }

        return null;
    }

    public function delete(int $id): bool
    {
        $salary = $this->findById($id);

        if ($salary) {
            return $salary->delete();
        }

        return false;
    }
}
