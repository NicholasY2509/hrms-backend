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
     * Get all teams.
     */
    public function index(TeamIndexRequest $request): JsonResponse
    {
        $teams = $this->repository->getPaginated(
            $request->only(['search']),
            $request->input('per_page', 15)
        );

        $resource = TeamResource::collection($teams);

        return $this->successResponse(
            $resource->response()->getData(true),
            'Teams retrieved successfully'
        );
    }

    /**
     * Store a new team.
     * 
     * @bodyParam name string required The name of the team.
     * @bodyParam department_id int required The ID of the department.
     * @bodyParam team_head_id int The ID of the employee who heads the team.
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
     * Get team details.
     */
    public function show(int $id): JsonResponse
    {
        $team = $this->repository->findById($id);

        if (!$team) {
            return $this->errorResponse('Team not found', 404);
        }

        return $this->successResponse(
            new TeamResource($team),
            'Team details retrieved'
        );
    }

    /**
     * Update a team.
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
     * Delete a team.
     */
    public function destroy(Team $team): JsonResponse
    {
        $this->service->deleteTeam($team);

        return $this->successResponse(null, 'Team deleted successfully');
    }
}
