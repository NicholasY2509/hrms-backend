<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\Employee;

class EmployeeRepository
{
    /**
     * Find employee by user ID.
     *
     * @param int $userId
     * @return Employee|null
     */
    public function findByUserId(int $userId): ?Employee
    {
        return Employee::query()
            ->whereHas('user_employee', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['user_employee.user', 'supervisor.employee', 'department', 'position'])
            ->first();
    }

    /**
     * Paginate employees with filters.
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = [])
    {
        return Employee::query()
            ->with(['position', 'department', 'work_location', 'user_employee.user'])
            ->filter($filters)
            ->paginate($perPage);
    }

    /**
     * Find employee by ID.
     *
     * @param int $id
     * @return Employee
     */
    public function findById(int $id): Employee
    {
        return Employee::query()
            ->with(['user_employee.user', 'supervisor.employee', 'department', 'position'])
            ->findOrFail($id);
    }

    /**
     * Create a new employee.
     *
     * @param array $data
     * @return Employee
     */
    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    /**
     * Update an existing employee.
     *
     * @param int $id
     * @param array $data
     * @return Employee
     */
    public function update(int $id, array $data): Employee
    {
        $employee = $this->findById($id);
        $employee->update($data);
        return $employee->fresh();
    }

    /**
     * Delete an employee.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $employee = $this->findById($id);
        return $employee->delete();
    }
}
