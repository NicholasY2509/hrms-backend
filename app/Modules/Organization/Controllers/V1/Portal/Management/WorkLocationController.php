<?php

namespace App\Modules\Organization\Controllers\V1\Portal\Management;

/**
 * @group Organization
 * @subgroup Management Portal
 */

use App\Http\Controllers\Controller;
use App\Modules\Organization\Models\WorkLocation;
use App\Modules\Organization\Repositories\WorkLocationRepository;
use App\Modules\Organization\Requests\WorkLocationIndexRequest;
use App\Modules\Organization\Requests\WorkLocationRequest;
use App\Modules\Organization\Resources\WorkLocationResource;
use App\Modules\Organization\Services\WorkLocationService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Organization
 * @subgroup Work Location
 */
class WorkLocationController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected WorkLocationRepository $repository,
        protected WorkLocationService $service
    ) {}

    /**
     * List work locations.
     * 
     * Get a paginated list of work locations with optional search.
     */
    public function index(WorkLocationIndexRequest $request): JsonResponse
    {
        $workLocations = $this->repository->getPaginated(
            $request->only('search'),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            WorkLocationResource::collection($workLocations)->response()->getData(true),
            'Work locations retrieved successfully'
        );
    }

    /**
     * Create work location.
     * 
     * Store a new work location in the system.
     */
    public function store(WorkLocationRequest $request): JsonResponse
    {
        $workLocation = $this->service->createWorkLocation($request->validated());

        return $this->successResponse(
            new WorkLocationResource($workLocation),
            'Work location created successfully',
            201
        );
    }

    /**
     * Get work location.
     * 
     * Get detailed information about a specific work location.
     */
    public function show(WorkLocation $workLocation): JsonResponse
    {
        return $this->successResponse(
            new WorkLocationResource($workLocation),
            'Work location details retrieved'
        );
    }

    /**
     * Update work location.
     * 
     * Update the details of an existing work location.
     */
    public function update(WorkLocationRequest $request, WorkLocation $workLocation): JsonResponse
    {
        $updatedWorkLocation = $this->service->updateWorkLocation($workLocation, $request->validated());

        return $this->successResponse(
            new WorkLocationResource($updatedWorkLocation),
            'Work location updated successfully'
        );
    }

    /**
     * Delete work location.
     * 
     * Remove a work location from the system.
     */
    public function destroy(WorkLocation $workLocation): JsonResponse
    {
        $this->service->deleteWorkLocation($workLocation);

        return $this->successResponse(null, 'Work location deleted successfully');
    }
}