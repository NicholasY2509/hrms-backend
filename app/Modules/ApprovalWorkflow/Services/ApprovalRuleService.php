<?php

namespace App\Modules\ApprovalWorkflow\Services;

use App\Modules\ApprovalWorkflow\Repositories\ApprovalRuleRepository;
use App\Modules\ApprovalWorkflow\Models\ApprovalRule;

class ApprovalRuleService
{
    public function __construct(
        protected ApprovalRuleRepository $repository
    ) {}

    public function createRule(array $data): ApprovalRule
    {
        return $this->repository->create($data);
    }

    public function updateRule(int $id, array $data): ?ApprovalRule
    {
        return $this->repository->update($id, $data);
    }

    public function deleteRule(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function updateRuleSteps(int $id, array $steps): bool
    {
        return $this->repository->update($id, ['steps' => $steps]) !== null;
    }
}
