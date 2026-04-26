<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalGroup;
use Illuminate\Database\Eloquent\Collection;

class ApprovalGroupRepository
{
    /**
     * Get paginated approval groups.
     */
    public function paginate(int $perPage = 15)
    {
        return ApprovalGroup::with('employees')->paginate($perPage);
    }

    /**
     * Create a new group.
     */
    public function create(array $data): ApprovalGroup
    {
        return ApprovalGroup::create($data);
    }

    /**
     * Find a group by ID.
     */
    public function find(int $id): ?ApprovalGroup
    {
        return ApprovalGroup::with('employees')->find($id);
    }

    /**
     * Sync employees to a group.
     */
    public function syncEmployees(ApprovalGroup $group, array $employeeIds): void
    {
        $group->employees()->sync($employeeIds);
    }

    /**
     * Delete a group.
     */
    public function delete(int $id): bool
    {
        return ApprovalGroup::destroy($id) > 0;
    }
}
