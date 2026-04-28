<?php

namespace App\Modules\ApprovalWorkflow\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\ApprovalWorkflow\Services\ApprovalStepTypeService;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalStepTypeResource;
use App\Modules\ApprovalWorkflow\Requests\V1\StoreApprovalStepTypeRequest;
use App\Modules\ApprovalWorkflow\Requests\V1\UpdateApprovalStepTypeRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Approval Workflow
 * @subgroup Master Data
 */
class ApprovalStepTypeController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ApprovalStepTypeService $service
    ) {}

    /**
     * List all available step types.
     * 
     * @queryParam search string Search by name or slug.
     * @queryParam per_page int Results per page.
     */
    public function index(Request $request): JsonResponse
    {
        $types = $this->service->paginateTypes($request->input('per_page', 15));
        $resource = ApprovalStepTypeResource::collection($types);
        
        return $this->successResponse($resource->response()->getData(true), 'Step types retrieved');
    }

    /**
     * Create a new approval step type.
     */
    public function store(StoreApprovalStepTypeRequest $request): JsonResponse
    {
        $type = $this->service->createType($request->validated());
        return $this->successResponse(new ApprovalStepTypeResource($type), 'Step type created', 201);
    }

    /**
     * Update an approval step type.
     */
    public function update(UpdateApprovalStepTypeRequest $request, $id): JsonResponse
    {
        $type = $this->service->updateType($id, $request->validated());
        if (!$type) return $this->errorResponse('Step type not found', 404);

        return $this->successResponse(new ApprovalStepTypeResource($type), 'Step type updated');
    }

    /**
     * Delete an approval step type.
     */
    public function destroy($id): JsonResponse
    {
        if ($this->service->deleteType($id)) {
            return $this->successResponse(null, 'Step type deleted');
        }

        return $this->errorResponse('Step type not found', 404);
    }
}
