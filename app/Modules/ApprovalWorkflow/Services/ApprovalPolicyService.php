<?php

namespace App\Modules\ApprovalWorkflow\Services;

use App\Modules\ApprovalWorkflow\Repositories\ApprovalPolicyRepository;
use App\Modules\ApprovalWorkflow\Models\ApprovalPolicy;
use Illuminate\Database\Eloquent\Collection;

class ApprovalPolicyService
{
    public function __construct(
        protected ApprovalPolicyRepository $repository
    ) {}

    public function paginatePolicies(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function createPolicy(array $data): ApprovalPolicy
    {
        return $this->repository->create($data);
    }

    public function getPolicy(int $id): ?ApprovalPolicy
    {
        return $this->repository->find($id);
    }

    public function updatePolicySteps(int $id, array $steps): bool
    {
        $policy = $this->repository->find($id);
        if (!$policy) return false;

        $this->repository->syncSteps($policy, $steps);
        return true;
    }

    public function deletePolicy(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
