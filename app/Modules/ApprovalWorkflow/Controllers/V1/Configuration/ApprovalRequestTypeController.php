<?php

namespace App\Modules\ApprovalWorkflow\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\ApprovalWorkflow\Services\ApprovalRequestTypeService;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestTypeResource;
use App\Modules\ApprovalWorkflow\Requests\V1\StoreApprovalRequestTypeRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Approval Workflow
 * @subgroup Request Types
 */
class ApprovalRequestTypeController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ApprovalRequestTypeService $service
    ) {}

    /**
     * List all available request types.
     * 
     * @queryParam search string Search by name or slug.
     * @queryParam per_page int Results per page.
     */
    public function index(Request $request): JsonResponse
    {
        $types = $this->service->paginateTypes($request->input('per_page', 15));
        $resource = ApprovalRequestTypeResource::collection($types);

        return $this->successResponse($resource->response()->getData(true), 'Request types retrieved');
    }

    /**
     * Create a new request type.
     */
    public function store(StoreApprovalRequestTypeRequest $request): JsonResponse
    {
        $type = $this->service->createType($request->validated());
        return $this->successResponse(new ApprovalRequestTypeResource($type), 'Request type created', 201);
    }

    /**
     * Update a request type.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $type = $this->service->updateType($id, $request->all());
        if (!$type) return $this->errorResponse('Request type not found', 404);

        return $this->successResponse(new ApprovalRequestTypeResource($type), 'Request type updated');
    }

    /**
     * Delete a request type.
     */
    public function destroy(int $id): JsonResponse
    {
        if ($this->service->deleteType($id)) {
            return $this->successResponse(null, 'Request type deleted');
        }

        return $this->errorResponse('Request type not found', 404);
    }
}
