<?php

namespace App\Modules\ApprovalWorkflow\Services;

use App\Modules\ApprovalWorkflow\Repositories\ApprovalStepTypeRepository;
use App\Modules\ApprovalWorkflow\Models\ApprovalStepType;
use Illuminate\Database\Eloquent\Collection;

class ApprovalStepTypeService
{
    public function __construct(
        protected ApprovalStepTypeRepository $repository
    ) {}

    public function getAllTypes(): Collection
    {
        return $this->repository->all();
    }

    public function paginateTypes(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function createType(array $data): ApprovalStepType
    {
        return $this->repository->create($data);
    }

    public function updateType(int $id, array $data): ?ApprovalStepType
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
