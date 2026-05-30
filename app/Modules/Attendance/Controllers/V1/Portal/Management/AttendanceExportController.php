<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Requests\AttendanceExportRequest;
use App\Modules\Attendance\Services\AttendanceExportService;
use App\Modules\System\Resources\V1\ReportResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance Management
 */
class AttendanceExportController extends Controller
{
    use ApiResponses;

    protected $service;

    public function __construct(AttendanceExportService $service)
    {
        $this->service = $service;
    }

    /**
     * Export Attendance Report
     * 
     * Start a background job to export attendance data based on the requested type.
     * 
     * @bodyParam type string required The type of report (daily_report, personal_report, team_report). Example: daily_report
     * @bodyParam format string required The output format (excel, pdf, csv, txt). Example: excel
     * @bodyParam start_date date required The start date for the report. Example: 2024-01-01
     * @bodyParam end_date date required The end date for the report. Example: 2024-01-31
     * @bodyParam employee_id int Optional employee ID (required if type is personal_report).
     * @bodyParam department_ids int[] Optional department IDs to filter.
     * @bodyParam team_ids int[] Optional team IDs to filter.
     * @bodyParam work_position_ids int[] Optional work position IDs to filter.
     * @bodyParam attendance_status_ids int[] Optional attendance status IDs to filter.
     * @bodyParam work_location_id int Optional work location ID to filter.
     * 
     * @response 202 {
     *  "success": true,
     *  "message": "Export started successfully.",
     *  "data": {
     *      "id": 1,
     *      "task_id": 10,
     *      "type": "daily_report",
     *      "status": "pending",
     *      "progress": 0
     *  }
     * }
     */
    public function export(AttendanceExportRequest $request): JsonResponse
    {
        $report = $this->service->export($request->validated());
        return $this->successResponse(new ReportResource($report), 'Export started successfully.', 202);
    }
}
