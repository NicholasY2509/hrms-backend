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
            ->with(['user_employee.user', 'supervisor.employee'])
            ->first();
    }
}
