<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\AttendanceLocation;
use App\Modules\Attendance\Requests\AttendanceLocationIndexRequest;
use App\Modules\Attendance\Requests\StoreAttendanceLocationRequest;
use App\Modules\Attendance\Requests\UpdateAttendanceLocationRequest;
use App\Modules\Attendance\Resources\AttendanceLocationResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance Management
 * 
 * Endpoints for managing master data of attendance locations (scan points).
 */
class AttendanceLocationManagementController extends Controller
{
    use ApiResponses;

    /**
     * List all attendance locations.
     */
    public function index(AttendanceLocationIndexRequest $request): JsonResponse
    {
        $locations = AttendanceLocation::with('work_location')
            ->filter($request->validated())
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->successResponse(
            AttendanceLocationResource::collection($locations)->response()->getData(true),
            'Daftar lokasi absensi berhasil diambil.'
        );
    }

    /**
     * Store a new attendance location.
     */
    public function store(StoreAttendanceLocationRequest $request): JsonResponse
    {
        $location = AttendanceLocation::create($request->validated());

        return $this->successResponse(
            new AttendanceLocationResource($location),
            'Lokasi absensi berhasil dibuat.',
            201
        );
    }

    /**
     * Show a specific attendance location.
     */
    public function show(AttendanceLocation $attendanceLocation): JsonResponse
    {
        return $this->successResponse(
            new AttendanceLocationResource($attendanceLocation),
            'Detail lokasi absensi berhasil diambil.'
        );
    }

    /**
     * Update an attendance location.
     */
    public function update(UpdateAttendanceLocationRequest $request, AttendanceLocation $attendanceLocation): JsonResponse
    {
        $attendanceLocation->update($request->validated());

        return $this->successResponse(
            new AttendanceLocationResource($attendanceLocation),
            'Lokasi absensi berhasil diperbarui.'
        );
    }

    /**
     * Remove an attendance location.
     */
    public function destroy(AttendanceLocation $attendanceLocation): JsonResponse
    {
        $attendanceLocation->delete();

        return $this->successResponse(
            null,
            'Lokasi absensi berhasil dihapus.'
        );
    }
}
