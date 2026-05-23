<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Requests\AttendanceCalculateRequest;
use App\Modules\Attendance\Requests\AttendanceIndexRequest;
use App\Modules\Attendance\Requests\BatchUpdateAttendanceStatusRequest;
use App\Modules\Attendance\Requests\UpdateAttendanceStatusRequest;
use App\Modules\Attendance\Resources\AttendanceManagementResource;
use App\Modules\Attendance\Services\AttendanceCalculationService;
use App\Modules\Attendance\Services\AttendanceService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance
 * @subgroup Management Portal
 */
class AttendanceManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected AttendanceService $service,
        protected AttendanceCalculationService $calculationService
    ) {}

    /**
     * List Attendances
     * 
     * Retrieves a paginated list of attendance records for all employees.
     * 
     * @response {
     *  "success": true,
     *  "message": "Attendances retrieved successfully",
     *  "data": [
     *    {
     *      "id": 1,
     *      "employee_id": 1,
     *      "employee": {
     *        "id": 1,
     *        "name": "John Doe",
     *        "nik": "123456"
     *      },
     *      "attendance_at": "2024-05-01",
     *      "working_hour": {
     *        "id": 1,
     *        "name": "Regular",
     *        "clock_in": "08:00",
     *        "clock_out": "17:00"
     *      },
     *      "check_in": "07:55:00",
     *      "check_out": "17:05:00",
     *      "status": "Present",
     *      "incoming_photo": "photos/in.jpg",
     *      "outgoing_photo": "photos/out.jpg",
     *      "incoming_location": { "id": 1, "name": "Main Office" },
     *      "outgoing_location": { "id": 1, "name": "Main Office" },
     *      "created_at": "2024-05-01T08:00:00.000000Z",
     *      "updated_at": "2024-05-01T17:00:00.000000Z"
     *    }
     *  ],
     *  "links": { ... },
     *  "meta": { ... }
     * }
     */
    public function index(AttendanceIndexRequest $request): JsonResponse
    {
        $attendances = $this->service->getPaginated(
            $request->validated(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            AttendanceManagementResource::collection($attendances)->response()->getData(true),
            'Attendances retrieved successfully'
        );
    }

    /**
     * Calculate Attendance
     * 
     * Starts an asynchronous job to calculate attendance records for a given date range.
     * Returns a task ID that can be used to track progress.
     * 
     * @bodyParam start_date date required The start date. Example: 2024-05-01
     * @bodyParam end_date date required The end date. Example: 2024-05-31
     * 
     * @response {
     *  "success": true,
     *  "message": "Attendance calculation started",
     *  "data": {
     *    "task_id": 1,
     *    "status": "pending"
     *  }
     * }
     */
    public function calculate(AttendanceCalculateRequest $request): JsonResponse
    {
        $task = $this->calculationService->initiateCalculation(
            $request->input('start_date'),
            $request->input('end_date'),
            $request->validated()
        );

        return $this->successResponse([
            'task_id' => $task->id,
            'status' => $task->status,
        ], 'Attendance calculation started');
    }

    /**
     * Update Attendance Status
     * 
     * Updates the attendance status for a specific attendance record.
     * 
     * @urlParam attendance int required The attendance ID. Example: 1
     * @bodyParam attendance_status_id int required The new attendance status ID. Example: 2
     * 
     * @response {
     *  "success": true,
     *  "message": "Attendance status updated successfully",
     *  "data": {
     *    "id": 1,
     *    "attendance_status": {
     *      "id": 2,
     *      "name": "Present"
     *    }
     *  }
     * }
     */
    public function updateStatus(int $attendance, UpdateAttendanceStatusRequest $request): JsonResponse
    {
        $updated = $this->service->updateStatus(
            $attendance,
            $request->input('attendance_status_id')
        );

        return $this->successResponse(
            new AttendanceManagementResource($updated),
            'Attendance status updated successfully'
        );
    }

    /**
     * Batch Update Attendance Status
     * 
     * Updates the attendance status for multiple attendance records at once.
     * 
     * @bodyParam attendance_ids int[] required Array of attendance IDs to update. Example: [1, 2, 3]
     * @bodyParam attendance_status_id int required The new attendance status ID. Example: 2
     * 
     * @response {
     *  "success": true,
     *  "message": "Attendance statuses updated successfully",
     *  "data": null
     * }
     */
    public function batchUpdateStatus(BatchUpdateAttendanceStatusRequest $request): JsonResponse
    {
        $this->service->batchUpdateStatus(
            $request->input('attendance_ids'),
            $request->input('attendance_status_id')
        );

        return $this->successResponse(
            null,
            'Attendance statuses updated successfully'
        );
    }
}

