<?php

namespace App\Modules\ApprovalWorkflow\Services;

use App\Modules\ApprovalWorkflow\Repositories\ApprovalRequestTypeRepository;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestType;

class ApprovalRequestTypeService
{
    public function __construct(
        protected ApprovalRequestTypeRepository $repository
    ) {}

    public function getAllActive()
    {
        return $this->repository->all();
    }

    public function paginateTypes(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function createType(array $data): ApprovalRequestType
    {
        return $this->repository->create($data);
    }

    public function updateType(int $id, array $data): ?ApprovalRequestType
    {
        $type = $this->repository->find($id);
        if (!$type) return null;

        $this->repository->update($type, $data);
        return $type;
    }

    public function deleteType(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
