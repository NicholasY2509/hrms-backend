<?php

namespace App\Modules\Attendance\Controllers;

use App\Http\Controllers\Controller;
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
}
