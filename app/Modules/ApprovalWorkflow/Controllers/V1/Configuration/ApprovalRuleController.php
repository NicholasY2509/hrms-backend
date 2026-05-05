<?php

namespace App\Modules\ApprovalWorkflow\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\ApprovalWorkflow\Services\ApprovalRuleService;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRuleResource;
use App\Modules\ApprovalWorkflow\Requests\V1\StoreApprovalRuleRequest;
use App\Modules\ApprovalWorkflow\Requests\V1\UpdateApprovalRuleRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Approval Workflow
 * @subgroup Rules
 */
class ApprovalRuleController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ApprovalRuleService $service
    ) {}

    /**
     * Create a new rule (Default or Position-specific) within a scheme.
     * 
     * @bodyParam approval_scheme_id integer required The ID of the approval scheme.
     * @bodyParam work_position_id integer optional The ID of the work position this rule applies to.
     * @bodyParam work_location_id integer optional The ID of the work location this rule applies to.
     * @bodyParam department_id integer optional The ID of the department this rule applies to.
     * @bodyParam is_default boolean optional Whether this is the default rule for the scheme.
     * @bodyParam is_active boolean optional Whether the rule is active.
     * @bodyParam steps array optional The steps for this rule.
     */
    public function store(StoreApprovalRuleRequest $request): JsonResponse
    {
        $rule = $this->service->createRule($request->validated());
        
        return $this->successResponse(
            new ApprovalRuleResource($rule), 
            'Rule created', 
            201
        );
    }

    /**
     * Update a rule and its steps.
     * 
     * @bodyParam work_position_id integer optional The ID of the work position.
     * @bodyParam work_location_id integer optional The ID of the work location.
     * @bodyParam department_id integer optional The ID of the department.
     * @bodyParam is_active boolean optional Whether the rule is active.
     * @bodyParam steps array optional The steps for this rule.
     */
    public function update(UpdateApprovalRuleRequest $request, $id): JsonResponse
    {
        $rule = $this->service->updateRule($id, $request->validated());
        if (!$rule) return $this->errorResponse('Rule not found', 404);
        
        return $this->successResponse(
            new ApprovalRuleResource($rule), 
            'Rule updated'
        );
    }

    /**
     * Delete a rule.
     */
    public function destroy($id): JsonResponse
    {
        if ($this->service->deleteRule($id)) {
            return $this->successResponse(null, 'Rule deleted');
        }

        return $this->errorResponse('Rule not found', 404);
    }
}
