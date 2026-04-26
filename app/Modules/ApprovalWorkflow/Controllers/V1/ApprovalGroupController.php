<?php

namespace App\Modules\ApprovalWorkflow\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\ApprovalWorkflow\Services\ApprovalGroupService;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalGroupResource;
use App\Modules\ApprovalWorkflow\Requests\V1\StoreApprovalGroupRequest;
use App\Modules\ApprovalWorkflow\Requests\V1\SyncGroupEmployeesRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Approval Workflow
 * @subgroup Groups
 */
class ApprovalGroupController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ApprovalGroupService $service
    ) {}

    /**
     * List all approval groups.
     */
    public function index(Request $request): JsonResponse
    {   
        $groups = $this->service->paginateGroups($request->input('per_page', 15));
        return $this->successResponse(ApprovalGroupResource::collection($groups), 'Groups retrieved');
    }

    /**
     * Create a new approval group.
     */
    public function store(StoreApprovalGroupRequest $request): JsonResponse
    {
        $group = $this->service->createGroup($request->validated());
        return $this->successResponse(new ApprovalGroupResource($group), 'Group created', 201);
    }

    /**
     * Get a specific approval group.
     */
    public function show(int $id): JsonResponse
    {
        $group = $this->service->getGroup($id);
        if (!$group) return $this->errorResponse('Group not found', 404);
        
        return $this->successResponse(new ApprovalGroupResource($group), 'Group retrieved');
    }

    /**
     * Sync employees to a group.
     * @bodyParam employee_ids array required List of employee IDs.
     */
    public function syncEmployees(SyncGroupEmployeesRequest $request, int $id): JsonResponse
    {
        if ($this->service->updateGroupEmployees($id, $request->employee_ids)) {
            return $this->successResponse(null, 'Employees synced to group');
        }

        return $this->errorResponse('Group not found', 404);
    }

    /**
     * Delete an approval group.
     */
    public function destroy(int $id): JsonResponse
    {
        if ($this->service->deleteGroup($id)) {
            return $this->successResponse(null, 'Group deleted');
        }

        return $this->errorResponse('Group not found', 404);
    }
}
