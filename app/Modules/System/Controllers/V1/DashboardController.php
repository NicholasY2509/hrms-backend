<?php

namespace App\Modules\System\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\System\Resources\DashboardResource;
use App\Modules\System\Services\DashboardService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Dashboard
 *
 * API for employee dashboard data aggregation.
 */
class DashboardController extends Controller
{
    use ApiResponses;

    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get aggregated dashboard data.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Dashboard data retrieved successfully",
     *  "data": {
     *      "employee": {...},
     *      "attendance": {...},
     *      "leave": { "pending_count": 2 },
     *      "holidays": [...],
     *      "tenure": "1 years and 2 months"
     *  }
     * }
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        $data = $this->dashboardService->getDashboardData($userId);

        return $this->successResponse(new DashboardResource($data), 'Dashboard data retrieved successfully');
    }
}
