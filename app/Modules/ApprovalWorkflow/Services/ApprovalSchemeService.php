<?php

namespace App\Modules\ApprovalWorkflow\Services;

use App\Modules\ApprovalWorkflow\Repositories\ApprovalSchemeRepository;
use App\Modules\ApprovalWorkflow\Models\ApprovalScheme;

class ApprovalSchemeService
{
    public function __construct(
        protected ApprovalSchemeRepository $repository
    ) {}

    public function paginateSchemes(array $filters = [], int $perPage = 15)
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function createScheme(array $data): ApprovalScheme
    {
        return $this->repository->create($data);
    }

    public function getSchemeDetails(int $id): ?ApprovalScheme
    {
        return $this->repository->findWithDetails($id);
    }

    public function updateScheme(int $id, array $data): bool
    {
        $scheme = $this->repository->find($id);
        if (!$scheme) return false;

        return $this->repository->update($scheme, $data);
    }

    public function deleteScheme(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
