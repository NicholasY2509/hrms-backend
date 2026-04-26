<?php

namespace App\Modules\ApprovalWorkflow\Services;

use App\Modules\ApprovalWorkflow\Repositories\ApprovalGroupRepository;
use App\Modules\ApprovalWorkflow\Models\ApprovalGroup;

class ApprovalGroupService
{
    public function __construct(
        protected ApprovalGroupRepository $repository
    ) {}

    public function paginateGroups(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function createGroup(array $data): ApprovalGroup
    {
        return $this->repository->create($data);
    }

    public function getGroup(int $id): ?ApprovalGroup
    {
        return $this->repository->find($id);
    }

    public function updateGroupEmployees(int $id, array $employeeIds): bool
    {
        $group = $this->repository->find($id);
        if (!$group) return false;

        $this->repository->syncEmployees($group, $employeeIds);
        return true;
    }

    public function deleteGroup(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
