<?php

namespace App\Modules\System\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\System\Resources\ManagementDashboardResource;
use App\Modules\System\Services\ManagementDashboardService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Dashboard
 *
 * API for management dashboard data aggregation.
 */
class ManagementDashboardController extends Controller
{
    use ApiResponses;

    protected ManagementDashboardService $dashboardService;

    public function __construct(ManagementDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get aggregated management dashboard data.
     * 
     * This endpoint provides comprehensive statistics for executives and HR management, 
     * covering workforce overview, attrition, attendance productivity, and payroll insights.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Management dashboard data retrieved successfully",
     *  "data": {
     *      "workforce_overview": {
     *          "headcount": { "total": 150, "active": 145, "inactive": 5 },
     *          "distribution": {
     *              "department": [{ "name": "IT", "count": 20 }],
     *              "location": [{ "name": "Head Office", "count": 100 }],
     *              "gender": [{ "label": "Male", "count": 80 }, { "label": "Female", "count": 65 }]
     *          },
     *          "growth_trend": [{ "month": "Jan 2024", "count": 140 }]
     *      },
     *      "attendance_productivity": {
     *          "today": { "present": 120, "late": 10, "absent": 5, "on_leave": 10, "attendance_rate": 89.5 },
     *          "monthly_overtime_hours": 150.5
     *      },
     *      "attrition_retention": {
     *          "total_resigned_6_months": 5,
     *          "turnover_rate_period": 3.4,
     *          "reasons_distribution": [{ "reason": "Personal", "count": 2 }]
     *      },
     *      "payroll_insights": {
     *          "total_monthly_payroll": 500000000,
     *          "department_cost_breakdown": [{ "name": "IT", "total": 150000000 }]
     *      },
     *      "pending_requests_count": { "leave": 5, "overtime": 3, "total": 8 }
     *  }
     * }
     */
    public function index(): JsonResponse
    {
        $data = $this->dashboardService->getDashboardData();

        return $this->successResponse(new ManagementDashboardResource($data), 'Management dashboard data retrieved successfully');
    }
}
