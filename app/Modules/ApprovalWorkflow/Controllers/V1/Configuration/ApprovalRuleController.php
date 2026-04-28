<?php

namespace App\Modules\ApprovalWorkflow\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\ApprovalWorkflow\Services\ApprovalRuleService;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRuleResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'approval_scheme_id' => 'required|exists:approval_schemes,id',
            'work_position_id' => 'nullable|exists:work_positions,id',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'steps' => 'nullable|array',
            'steps.*.type_slug' => 'required|string',
            'steps.*.target_id' => 'nullable|integer',
            'steps.*.sequence' => 'nullable|integer',
        ]);

        $rule = $this->service->createRule($validated);
        
        return $this->successResponse(
            new ApprovalRuleResource($rule), 
            'Rule created', 
            201
        );
    }

    /**
     * Update a rule and its steps.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => 'boolean',
            'steps' => 'nullable|array',
            'steps.*.type_slug' => 'required|string',
            'steps.*.target_id' => 'nullable|integer',
            'steps.*.sequence' => 'nullable|integer',
        ]);

        $rule = $this->service->updateRule($id, $validated);
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
