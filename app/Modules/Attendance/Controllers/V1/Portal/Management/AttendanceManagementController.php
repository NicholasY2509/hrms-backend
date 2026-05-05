<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Requests\AttendanceIndexRequest;
use App\Modules\Attendance\Resources\AttendanceManagementResource;
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
        protected \App\Modules\Attendance\Services\AttendanceService $service
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
}
