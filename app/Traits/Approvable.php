<?php

namespace App\Traits;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalScheme;
use App\Modules\ApprovalWorkflow\Repositories\ApprovalRuleRepository;
use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Log;

trait Approvable
{
    /**
     * Boot the trait and register the 'created' event listener.
     */
    protected static function bootApprovable(): void
    {
        static::created(function ($model) {
            $model->initializeApprovalFlow();
        });
    }

    /**
     * Get the approval request associated with the model.
     */
    public function approvalRequest(): MorphOne
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable');
    }

    /**
     * Initialize the approval flow for this model.
     */
    public function initializeApprovalFlow(): void
    {
        try {
            // 1. Identify the Scheme
            $scheme = ApprovalScheme::where('model_class', get_class($this))->first();
            
            if (!$scheme) {
                Log::warning("No approval scheme found for " . get_class($this));
                return;
            }

            // 2. Determine Applicant's Work Position
            $workPositionId = $this->resolveApplicantWorkPositionId();

            // 3. Find the Best Matching Rule (Specific Position -> Global Default)
            $repository = app(ApprovalRuleRepository::class);
            $rule = $repository->findBestMatch($scheme->id, $workPositionId);

            if (!$rule) {
                Log::warning("No matching approval rule found for scheme {$scheme->id} and position {$workPositionId}");
                return;
            }

            // 4. Create the runtime Approval Request
            $request = $this->approvalRequest()->create([
                'approval_rule_id' => $rule->id,
                'reference_number' => $this->document_no ?? $this->document_number ?? null,
                'status' => 'pending',
                'current_step_sequence' => 1,
            ]);

            // 5. Snapshot and Resolve Steps
            foreach ($rule->steps as $step) {
                $approverId = $this->resolveStepApproverId($step);
                
                $request->steps()->create([
                    'approver_type' => $step->type_slug,
                    'approver_id' => $approverId,
                    'sequence' => $step->sequence,
                    'status' => 'pending',
                ]);
            }
            
            Log::info("Approval flow initialized for " . get_class($this) . " ID: {$this->id} using Rule ID: {$rule->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to initialize approval flow for " . get_class($this) . ": " . $e->getMessage());
        }
    }

    /**
     * Resolve the applicant's work position from the model.
     */
    protected function resolveApplicantWorkPositionId(): ?int
    {
        // Try direct relationship
        if (isset($this->employee) && $this->employee->work_position_id) {
            return $this->employee->work_position_id;
        }

        // Try through user relationship
        if (isset($this->user->employee) && $this->user->employee->work_position_id) {
            return $this->user->employee->work_position_id;
        }

        return null;
    }

    /**
     * Resolve the actual User ID or Group ID for a specific rule step.
     */
    protected function resolveStepApproverId($step): ?int
    {
        switch ($step->type_slug) {
            case 'user':
            case 'group':
                return $step->target_id;
            
            case 'supervisor':
                return $this->resolveSupervisorId();
                
            case 'dept_head':
                return $this->resolveDeptHeadId();
                
            default:
                return null;
        }
    }

    /**
     * Logic to find the direct supervisor's User ID.
     */
    protected function resolveSupervisorId(): ?int
    {
        $employee = $this->employee ?? $this->user->employee ?? null;
        
        // Using the new team_head_id from the Teams table
        if ($employee && $employee->team_id && $employee->team) {
            return $employee->team->team_head_id;
        }
        
        return null;
    }

    /**
     * Logic to find the Department Head's User ID.
     */
    protected function resolveDeptHeadId(): ?int
    {
        $employee = $this->employee ?? $this->user->employee ?? null;
        
        // Using the new dept_head_id from the Departments table
        if ($employee && $employee->department_id && $employee->department) {
            return $employee->department->dept_head_id;
        }
        
        return null;
    }
}
