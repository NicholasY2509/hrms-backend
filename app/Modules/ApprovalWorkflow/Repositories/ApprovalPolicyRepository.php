<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalPolicy;
use App\Modules\ApprovalWorkflow\Models\ApprovalPolicyStep;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ApprovalPolicyRepository
{
    /**
     * Get paginated policies with their steps.
     */
    public function paginate(int $perPage = 15)
    {
        return ApprovalPolicy::with(['steps.group', 'steps.employee'])->paginate($perPage);
    }

    /**
     * Create a new policy.
     */
    public function create(array $data): ApprovalPolicy
    {
        return ApprovalPolicy::create($data);
    }

    /**
     * Find a policy by ID.
     */
    public function find(int $id): ?ApprovalPolicy
    {
        return ApprovalPolicy::with(['steps.group', 'steps.employee'])->find($id);
    }

    /**
     * Replace all steps for a policy.
     */
    public function syncSteps(ApprovalPolicy $policy, array $stepsData): void
    {
        DB::transaction(function () use ($policy, $stepsData) {
            $policy->steps()->delete();
            
            foreach ($stepsData as $index => $step) {
                $policy->steps()->create([
                    'type_slug' => $step['type_slug'],
                    'target_id' => $step['target_id'] ?? null,
                    'sequence' => $step['sequence'] ?? ($index + 1),
                ]);
            }
        });
    }

    /**
     * Delete a policy.
     */
    public function delete(int $id): bool
    {
        return ApprovalPolicy::destroy($id) > 0;
    }
}
