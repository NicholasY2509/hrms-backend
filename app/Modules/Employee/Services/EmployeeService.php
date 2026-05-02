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

    /**
     * List employees with pagination and filters.
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listEmployees(int $perPage = 15, array $filters = [])
    {
        return $this->employeeRepository->paginate($perPage, $filters);
    }

    /**
     * Get an employee by ID.
     *
     * @param int $id
     * @return Employee
     */
    public function getEmployee(int $id): Employee
    {
        return $this->employeeRepository->findById($id);
    }

    /**
     * Create a new employee.
     *
     * @param array $data
     * @return Employee
     */
    public function createEmployee(array $data): Employee
    {
        // Business logic for creation can go here (e.g., firing events, sending emails)
        return $this->employeeRepository->create($data);
    }

    /**
     * Update an employee.
     *
     * @param int $id
     * @param array $data
     * @return Employee
     */
    public function updateEmployee(int $id, array $data): Employee
    {
        // Business logic for update can go here
        return $this->employeeRepository->update($id, $data);
    }

    /**
     * Delete an employee.
     *
     * @param int $id
     * @return bool
     */
    public function deleteEmployee(int $id): bool
    {
        // Business logic for deletion can go here
        return $this->employeeRepository->delete($id);
    }
}
