<?php

namespace App\Modules\ApprovalWorkflow\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\ApprovalWorkflow\Services\ApprovalPolicyService;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalPolicyResource;
use App\Modules\ApprovalWorkflow\Requests\V1\StoreApprovalPolicyRequest;
use App\Modules\ApprovalWorkflow\Requests\V1\UpdatePolicyStepsRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Approval Workflow
 * @subgroup Policies
 */
class ApprovalPolicyController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ApprovalPolicyService $service
    ) {}

    /**
     * List all approval policies.
     */
    public function index(Request $request): JsonResponse
    {
        $policies = $this->service->paginatePolicies($request->get('per_page', 15));
        return $this->successResponse(ApprovalPolicyResource::collection($policies), 'Policies retrieved');
    }

    /**
     * Create a new approval policy.
     */
    public function store(StoreApprovalPolicyRequest $request): JsonResponse
    {
        $policy = $this->service->createPolicy($request->validated());
        return $this->successResponse(new ApprovalPolicyResource($policy), 'Policy created', 201);
    }

    /**
     * Get a specific approval policy.
     */
    public function show(int $id): JsonResponse
    {
        $policy = $this->service->getPolicy($id);
        if (!$policy) return $this->errorResponse('Policy not found', 404);
        
        return $this->successResponse(new ApprovalPolicyResource($policy), 'Policy retrieved');
    }

    /**
     * Update the steps for a policy.
     */
    public function updateSteps(UpdatePolicyStepsRequest $request, int $id): JsonResponse
    {
        if ($this->service->updatePolicySteps($id, $request->steps)) {
            return $this->successResponse(null, 'Policy steps updated');
        }

        return $this->errorResponse('Policy not found', 404);
    }

    /**
     * Delete an approval policy.
     */
    public function destroy(int $id): JsonResponse
    {
        if ($this->service->deletePolicy($id)) {
            return $this->successResponse(null, 'Policy deleted');
        }

        return $this->errorResponse('Policy not found', 404);
    }
}
