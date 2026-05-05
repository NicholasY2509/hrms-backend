<?php

namespace App\Modules\Attendance\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Resources\AttendanceStatusResource;
use App\Modules\Attendance\Services\AttendanceService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance
 * @subgroup Configuration
 */
class AttendanceStatusController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected AttendanceService $service
    ) {}

    /**
     * List Attendance Statuses
     * 
     * Retrieves all available attendance statuses.
     * 
     * @response {
     *  "success": true,
     *  "message": "Attendance statuses retrieved successfully",
     *  "data": [
     *    {
     *      "id": 1,
     *      "name": "Hadir",
     *      "color": "#10b981"
     *    },
     *    {
     *      "id": 2,
     *      "name": "Terlambat",
     *      "color": "#f59e0b"
     *    }
     *  ]
     * }
     */
    public function index(): JsonResponse
    {
        $statuses = $this->service->getAllStatuses();

        return $this->successResponse(
            AttendanceStatusResource::collection($statuses),
            'Attendance statuses retrieved successfully'
        );
    }
}
