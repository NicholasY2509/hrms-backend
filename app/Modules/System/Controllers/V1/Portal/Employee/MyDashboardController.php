<?php

namespace App\Modules\System\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\System\Resources\MyDashboardResource;
use App\Modules\System\Services\MyDashboardService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * @group Dashboard
 *
 * API for employee web dashboard (MyDashboard) data aggregation.
 */
class MyDashboardController extends Controller
{
    use ApiResponses;

    protected MyDashboardService $dashboardService;

    /**
     * Inject MyDashboardService dependency.
     */
    public function __construct(MyDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get aggregated web dashboard data for the authenticated employee.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Web dashboard data retrieved successfully",
     *  "data": {
     *      "employee": {...},
     *      "attendance": {...},
     *      "pending_requests": [...],
     *      "holidays": [...],
     *      "tenure": "1 years and 2 months",
     *      "attendance_summary": [...],
     *      "attendance_rate": 95.5,
     *      "recent_attendance": [...]
     *  }
     * }
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        $data = Cache::remember('employee_dashboard_' . $userId, 300, function () use ($userId) {
            $raw = $this->dashboardService->getDashboardData($userId);
            // Resolve the resource into a plain array before caching to prevent __PHP_Incomplete_Class errors
            return (new MyDashboardResource($raw))->resolve();
        });

        return $this->successResponse($data, 'Web dashboard data retrieved successfully');
    }
}
