<?php

namespace App\Modules\Employee\Services;

use App\Modules\Employee\Models\EmployeeStatus;
use App\Modules\Employee\Repositories\EmployeeStatusRepository;
use Illuminate\Support\Facades\DB;

class EmployeeStatusService
{
    public function __construct(
        protected EmployeeStatusRepository $repository
    ) {}

    /**
     * Create a new employee status.
     */
    public function createStatus(array $data): EmployeeStatus
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    /**
     * Update an existing employee status.
     */
    public function updateStatus(EmployeeStatus $status, array $data): EmployeeStatus
    {
        return DB::transaction(function () use ($status, $data) {
            return $this->repository->update($status, $data);
        });
    }

    /**
     * Delete an employee status.
     */
    public function deleteStatus(EmployeeStatus $status): bool
    {
        return DB::transaction(function () use ($status) {
            return $this->repository->delete($status);
        });
    }
}
