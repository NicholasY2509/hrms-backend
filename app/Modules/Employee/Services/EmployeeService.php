<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Repositories\EmployeeRepository;

class EmployeeService
{
    protected EmployeeRepository $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Get the profile for a specific user ID.
     *
     * @param int $userId
     * @return Employee|null
     */
    public function getProfile(int $userId): ?Employee
    {
        return $this->employeeRepository->findByUserId($userId);
    }
}
