<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Requests\ClockInRequest;
use App\Modules\Attendance\Requests\ClockOutRequest;
use App\Modules\Attendance\Requests\GetAttendanceHistoryRequest;
use App\Modules\Attendance\Resources\AttendanceResource;
use App\Modules\Attendance\Services\MobileAttendanceService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Attendance
 * @subgroup Employee Portal
 *
 * API for managing personal attendance.
 */
class MyAttendanceController extends Controller
{
    use ApiResponses;

    protected MobileAttendanceService $mobileAttendanceService;

    public function __construct(MobileAttendanceService $mobileAttendanceService)
    {
        $this->mobileAttendanceService = $mobileAttendanceService;
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
        $data = $this->mobileAttendanceService->getHistoryWithSummary(Auth::id(), $request->validated());

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
        $attendance = $this->mobileAttendanceService->getUserStatus($userId);

        if (!$attendance) {
            return $this->successResponse(null, 'No attendance record found for today');
        }

        return $this->successResponse(new AttendanceResource($attendance), 'Current status retrieved');
    }

    /**
     * Get working hour for a specific date.
     * 
     * @queryParam date date The date to retrieve (Y-m-d). Example: 2026-04-21
     * 
     * @response {
     *  "status": "Success",
     *  "message": "Working hour retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "date": "2026-04-21",
     *      "shift_start": "2026-04-21 08:30:00",
     *      "shift_end": "2026-04-21 17:00:00"
     *  }
     * }
     */
    public function workingHour(\Illuminate\Http\Request $request): JsonResponse
    {
        $userId = Auth::id();
        $date = $request->query('date', \Carbon\Carbon::now()->format('Y-m-d'));

        $workingHour = $this->mobileAttendanceService->getWorkingHourByDate($userId, $date);

        if (!$workingHour) {
            return $this->errorResponse('Data Jam Kerja tidak ditemukan untuk tanggal tersebut!', 404);
        }

        return $this->successResponse(
            new \App\Modules\Attendance\Resources\AttendanceWorkingHourResource($workingHour),
            'Working hour retrieved successfully'
        );
    }

    /**
     * Check if the user is within a valid geofence for attendance.
     * 
     * @bodyParam latitude float required The latitude of the user's current location.
     * @bodyParam longitude float required The longitude of the user's current location.
     */
    public function checkLocation(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $isValid = $this->mobileAttendanceService->checkUserLocation(
            Auth::id(),
            $request->latitude,
            $request->longitude
        );

        if ($isValid) {
            return $this->successResponse([
                'is_valid' => true,
            ], 'Location is valid for attendance');
        }

        return $this->errorResponse('Anda berada di luar area absensi yang valid!', 422);
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
        $attendance = $this->mobileAttendanceService->clockIn(Auth::id(), $request->validated());

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
        $attendance = $this->mobileAttendanceService->clockOut(Auth::id(), $request->validated());

        return $this->successResponse(new AttendanceResource($attendance), 'Berhasil melakukan absensi pulang!');
    }
}

