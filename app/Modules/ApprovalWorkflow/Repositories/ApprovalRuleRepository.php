<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalRule;
use App\Modules\ApprovalWorkflow\Models\ApprovalRuleStep;
use Illuminate\Support\Facades\DB;

class ApprovalRuleRepository
{
    /**
     * Create a new rule with steps.
     */
    public function create(array $data): ApprovalRule
    {
        return DB::transaction(function () use ($data) {
            $steps = $data['steps'] ?? [];
            unset($data['steps']);

            $rule = ApprovalRule::create($data);

            if (!empty($steps)) {
                $this->syncSteps($rule, $steps);
            }

            return $rule->load(['steps.group', 'steps.employee']);
        });
    }

    /**
     * Update an existing rule and its steps.
     */
    public function update(int $id, array $data): ?ApprovalRule
    {
        return DB::transaction(function () use ($id, $data) {
            $rule = ApprovalRule::find($id);
            if (!$rule) return null;

            $steps = $data['steps'] ?? null;
            unset($data['steps']);

            $rule->update($data);

            if ($steps !== null) {
                $this->syncSteps($rule, $steps);
            }

            return $rule->load(['steps.group', 'steps.employee']);
        });
    }

    /**
     * Find the best matching rule for a scheme and work position.
     */
    public function findBestMatch(int $schemeId, ?int $workPositionId = null): ?ApprovalRule
    {
        // 1. Try specific position
        if ($workPositionId) {
            $rule = ApprovalRule::where('approval_scheme_id', $schemeId)
                ->where('work_position_id', $workPositionId)
                ->where('is_active', true)
                ->with(['steps.group', 'steps.employee'])
                ->first();
                
            if ($rule) return $rule;
        }

        // 2. Try global default
        return ApprovalRule::where('approval_scheme_id', $schemeId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->with(['steps.group', 'steps.employee'])
            ->first();
    }

    /**
     * Replace all steps for a rule.
     */
    public function syncSteps(ApprovalRule $rule, array $stepsData): void
    {
        DB::transaction(function () use ($rule, $stepsData) {
            $rule->steps()->delete();
            
            foreach ($stepsData as $index => $step) {
                $rule->steps()->create([
                    'type_slug' => $step['type_slug'],
                    'target_id' => $step['target_id'] ?? null,
                    'sequence' => $step['sequence'] ?? ($index + 1),
                ]);
            }
        });
    }

    public function delete(int $id): bool
    {
        return ApprovalRule::destroy($id) > 0;
    }
}
