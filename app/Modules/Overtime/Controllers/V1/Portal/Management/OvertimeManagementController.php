<?php

namespace App\Modules\Overtime\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Overtime\Repositories\OvertimeRepository;
use App\Modules\Overtime\Resources\V1\OvertimeResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Overtime
 * @subgroup Management Portal
 * 
 * Endpoints for HR/Management to monitor all employee overtime requests.
 */
class OvertimeManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected OvertimeRepository $repository
    ) {}

    /**
     * List all employee overtime requests.
     * 
     * @queryParam employee_id int Filter by employee.
     * @queryParam type string Filter by type (UMUM, DAC, NATIONAL).
     * @queryParam start_date date Filter by start date (Y-m-d).
     * @queryParam end_date date Filter by end date (Y-m-d).
     * @queryParam per_page int Results per page.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['employee_id', 'type', 'start_date', 'end_date', 'is_settled']);
        $perPage = $request->query('per_page', 15);

        $overtimes = $this->repository->paginate($filters, $perPage);

        return $this->successResponse(
            OvertimeResource::collection($overtimes)->response()->getData(true),
            'All employee overtime requests retrieved successfully.'
        );
    }

    /**
     * Show overtime detail with approval progress.
     */
    public function show(int $id): JsonResponse
    {
        $overtime = $this->repository->find($id);

        if (!$overtime) {
            return $this->errorResponse('Overtime request not found.', 404);
        }

        return $this->successResponse(
            new OvertimeResource($overtime),
            'Overtime detail retrieved.'
        );
    }
}
