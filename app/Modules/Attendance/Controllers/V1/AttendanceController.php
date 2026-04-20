<?php

namespace App\Modules\Attendance\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Requests\ClockInRequest;
use App\Modules\Attendance\Requests\ClockOutRequest;
use App\Modules\Attendance\Requests\GetAttendanceHistoryRequest;
use App\Modules\Attendance\Resources\AttendanceResource;
use App\Modules\Attendance\Services\AttendanceService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Attendance
 *
 * API for managing attendance.
 */
class AttendanceController extends Controller
{
    use ApiResponses;

    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Get attendance history for the authenticated user.
     * 
     * @queryParam start_date date required The start date of the range (Y-m-d). Example: 2026-04-01
     * @queryParam end_date date required The end date of the range (Y-m-d). Example: 2026-04-30
     * 
     * @response {
     *  "status": "Success",
     *  "message": "Attendance history retrieved",
     *  "data": {
     *      "records": [...],
     *      "summary": [...]
     *  }
     * }
     */
    public function index(GetAttendanceHistoryRequest $request): JsonResponse
    {
        $data = $this->attendanceService->getHistoryWithSummary(Auth::id(), $request->validated());

        return $this->successResponse([
            'records' => AttendanceResource::collection($data['records']),
            'summary' => $data['summary'],
        ], 'Attendance history retrieved');
    }

    /**
     * Get the current user's attendance status for today.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Current status retrieved",
     *  "data": {
     *      "id": 1,
     *      "attendance_at": "2026-04-19",
     *      "check_in": "08:00:00",
     *      "check_out": "17:00:00",
     *      "status": "Present",
     *      "all_scans": [...],
     *      "incoming_photo": "...",
     *      "outgoing_photo": "..."
     *  }
     * }
     */
    public function status(): JsonResponse
    {
        $userId = Auth::id();
        $attendance = $this->attendanceService->getUserStatus($userId);

        if (!$attendance) {
            return $this->successResponse(null, 'No attendance record found for today');
        }

        return $this->successResponse(new AttendanceResource($attendance), 'Current status retrieved');
    }

    /**
     * Perform clock-in for the authenticated user.
     * 
     * @bodyParam latitude float required The latitude of the user's current location. Example: 3.5456069
     * @bodyParam longitude float required The longitude of the user's current location. Example: 98.6984721
     * @bodyParam photo file The photo taken during clock-in.
     * 
     * @response {
     *  "status": "Success",
     *  "message": "Berhasil melakukan absensi masuk!",
     *  "data": {...}
     * }
     */
    public function clockIn(ClockInRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->clockIn(Auth::id(), $request->validated());

        return $this->successResponse(new AttendanceResource($attendance), 'Berhasil melakukan absensi masuk!');
    }

    /**
     * Perform clock-out for the authenticated user.
     * 
     * @bodyParam latitude float required The latitude of the user's current location. Example: 3.5456069
     * @bodyParam longitude float required The longitude of the user's current location. Example: 98.6984721
     * @bodyParam photo file The photo taken during clock-out.
     * 
     * @response {
     *  "status": "Success",
     *  "message": "Berhasil melakukan absensi pulang!",
     *  "data": {...}
     * }
     */
    public function clockOut(ClockOutRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->clockOut(Auth::id(), $request->validated());

        return $this->successResponse(new AttendanceResource($attendance), 'Berhasil melakukan absensi pulang!');
    }
}

