<?php

namespace App\Modules\Organization\Controllers\V1\Portal\Management;

/**
 * @group Organization
 * @subgroup Management Portal
 */

use App\Http\Controllers\Controller;
use App\Modules\Organization\Models\Team;
use App\Modules\Organization\Repositories\TeamRepository;
use App\Modules\Organization\Requests\TeamIndexRequest;
use App\Modules\Organization\Requests\TeamRequest;
use App\Modules\Organization\Resources\TeamResource;
use App\Modules\Organization\Services\TeamService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Organization
 * @subgroup Team
 */
class TeamController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected TeamRepository $repository,
        protected TeamService $service
    ) {}

    /**
     * List teams.
     * 
     * Get a paginated list of teams with optional search.
     */
    public function index(TeamIndexRequest $request): JsonResponse
    {
        $teams = $this->repository->getPaginated(
            $request->only(['search', 'work_location_id']),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            TeamResource::collection($teams)->response()->getData(true),
            'Teams retrieved successfully'
        );
    }

    /**
     * Create team.
     * 
     * Store a new team in the system.
     */
    public function store(TeamRequest $request): JsonResponse
    {
        $team = $this->service->createTeam($request->validated());

        return $this->successResponse(
            new TeamResource($team),
            'Team created successfully',
            201
        );
    }

    /**
     * Get team.
     * 
     * Get detailed information about a specific team.
     */
    public function show(Team $team): JsonResponse
    {
        return $this->successResponse(
            new TeamResource($team->load(['workLocation', 'head'])),
            'Team details retrieved'
        );
    }

    /**
     * Update team.
     * 
     * Update the details of an existing team.
     */
    public function update(TeamRequest $request, Team $team): JsonResponse
    {
        $updatedTeam = $this->service->updateTeam($team, $request->validated());

        return $this->successResponse(
            new TeamResource($updatedTeam),
            'Team updated successfully'
        );
    }

    /**
     * Delete team.
     * 
     * Remove a team from the system.
     */
    public function destroy(Team $team): JsonResponse
    {
        $this->service->deleteTeam($team);

        return $this->successResponse(null, 'Team deleted successfully');
    }
}
