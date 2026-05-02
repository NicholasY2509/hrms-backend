<?php

namespace App\Modules\Organization\Controllers\V1\Portal\Management;

/**
 * @group Organization
 * @subgroup Management Portal
 */

use App\Http\Controllers\Controller;
use App\Modules\Organization\Models\WorkPosition;
use App\Modules\Organization\Repositories\WorkPositionRepository;
use App\Modules\Organization\Requests\WorkPositionIndexRequest;
use App\Modules\Organization\Requests\WorkPositionRequest;
use App\Modules\Organization\Resources\WorkPositionResource;
use App\Modules\Organization\Services\WorkPositionService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Organization
 * @subgroup Work Position
 */
class WorkPositionController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected WorkPositionRepository $repository,
        protected WorkPositionService $service
    ) {}

    /**
     * List work positions.
     * 
     * Get a paginated list of work positions with optional search.
     */
    public function index(WorkPositionIndexRequest $request): JsonResponse
    {
        $workPositions = $this->repository->getPaginated(
            $request->only('search'),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            WorkPositionResource::collection($workPositions)->response()->getData(true),
            'Work positions retrieved successfully'
        );
    }

    /**
     * Create work position.
     * 
     * Store a new work position in the system.
     */
    public function store(WorkPositionRequest $request): JsonResponse
    {
        $workPosition = $this->service->createWorkPosition($request->validated());

        return $this->successResponse(
            new WorkPositionResource($workPosition),
            'Work position created successfully',
            201
        );
    }

    /**
     * Get work position.
     * 
     * Get detailed information about a specific work position.
     */
    public function show(WorkPosition $workPosition): JsonResponse
    {
        return $this->successResponse(
            new WorkPositionResource($workPosition->load(['criteria', 'approvals'])),
            'Work position details retrieved'
        );
    }

    /**
     * Update work position.
     * 
     * Update the details of an existing work position.
     */
    public function update(WorkPositionRequest $request, WorkPosition $workPosition): JsonResponse
    {
        $updatedWorkPosition = $this->service->updateWorkPosition($workPosition, $request->validated());

        return $this->successResponse(
            new WorkPositionResource($updatedWorkPosition),
            'Work position updated successfully'
        );
    }

    /**
     * Delete work position.
     * 
     * Remove a work position from the system.
     */
    public function destroy(WorkPosition $workPosition): JsonResponse
    {
        $this->service->deleteWorkPosition($workPosition);

        return $this->successResponse(null, 'Work position deleted successfully');
    }
}
