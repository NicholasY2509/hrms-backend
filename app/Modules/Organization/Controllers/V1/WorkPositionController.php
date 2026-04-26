<?php

namespace App\Modules\Organization\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\Organization\Models\WorkPosition;
use App\Modules\Organization\Repositories\WorkPositionRepository;
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
     * Get all work positions.
     * 
     * @queryParam search string Search by name or alias.
     * @queryParam per_page int Results per page.
     */
    public function index(Request $request): JsonResponse
    {
        $workPositions = $this->repository->getPaginated(
            $request->only('search'),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            WorkPositionResource::collection($workPositions),
            'Work positions retrieved successfully'
        );
    }

    /**
     * Store a new work position.
     * 
     * @bodyParam name string required The name of the position.
     * @bodyParam alias string The alias of the position.
     * @bodyParam uang_makan number required
     * @bodyParam criteria array List of criteria.
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
     * Get work position details.
     */
    public function show(int $id): JsonResponse
    {
        $workPosition = $this->repository->findById($id);

        if (!$workPosition) {
            return $this->errorResponse('Work position not found', 404);
        }

        return $this->successResponse(
            new WorkPositionResource($workPosition),
            'Work position details retrieved'
        );
    }

    /**
     * Update a work position.
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
     * Delete a work position.
     */
    public function destroy(WorkPosition $workPosition): JsonResponse
    {
        $this->service->deleteWorkPosition($workPosition);

        return $this->successResponse(null, 'Work position deleted successfully');
    }
}
