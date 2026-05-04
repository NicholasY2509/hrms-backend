<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\EmployeeStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EmployeeStatusRepository
{
    /**
     * Get all employee statuses.
     */
    public function all(): Collection
    {
        return EmployeeStatus::orderBy('name')->get();
    }

    /**
     * Paginate employee statuses.
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = EmployeeStatus::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Find an employee status by ID.
     */
    public function find(int $id): ?EmployeeStatus
    {
        return EmployeeStatus::find($id);
    }

    /**
     * Create a new employee status.
     */
    public function create(array $data): EmployeeStatus
    {
        return EmployeeStatus::create($data);
    }

    /**
     * Update an employee status.
     */
    public function update(EmployeeStatus $status, array $data): EmployeeStatus
    {
        $status->update($data);
        return $status;
    }

    /**
     * Delete an employee status.
     */
    public function delete(EmployeeStatus $status): ?bool
    {
        return $status->delete();
    }
}
