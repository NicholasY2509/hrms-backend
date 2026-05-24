<?php

namespace App\Modules\Organization\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\Employee;
use App\Modules\Organization\Services\OrganizationChartService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

class OrganizationChartController extends Controller
{
    use ApiResponses;

    /**
     * @var OrganizationChartService
     */
    protected $chartService;

    public function __construct(OrganizationChartService $chartService)
    {
        $this->chartService = $chartService;
    }

    /**
     * Get Organization Chart
     *
     * @group Organization Chart
     * @response {
     *  "success": true,
     *  "data": {
     *      "nodes": [
     *          {"id": "1", "type": "positionNode", "data": {"label": "CEO", "alias": "Chief Exec"}}
     *      ],
     *      "edges": [
     *          {"id": "edge_1_2", "source": "1", "target": "2"}
     *      ]
     *  },
     *  "message": "Organization chart data retrieved successfully."
     * }
     */
    public function index(): JsonResponse
    {
        $data = $this->chartService->getChartData();

        return $this->successResponse($data, 'Organization chart data retrieved successfully.');
    }

    /**
     * Get Employees by Position
     *
     * @group Organization Chart
     */
    public function employees($positionId): JsonResponse
    {
        $employees = Employee::with(['department', 'work_location'])
            ->where('work_position_id', $positionId)
            ->where('work_employee_status_id', 1)
            ->get();

        return $this->successResponse($employees, 'Employees retrieved successfully.');
    }
}
