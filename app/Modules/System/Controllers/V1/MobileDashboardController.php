<?php

namespace App\Modules\System\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\System\Resources\MobileDashboardResource;
use App\Modules\System\Services\MobileDashboardService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Dashboard
 *
 * API for employee mobile dashboard data aggregation.
 */
class MobileDashboardController extends Controller
{
    use ApiResponses;

    protected MobileDashboardService $dashboardService;

    public function __construct(MobileDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get aggregated mobile dashboard data.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Mobile dashboard data retrieved successfully",
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

        return $this->successResponse(new MobileDashboardResource($data), 'Mobile dashboard data retrieved successfully');
    }
}
