<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\AttendanceUser;
use App\Modules\Attendance\Requests\AttendanceUserIndexRequest;
use App\Modules\Attendance\Requests\StoreAttendanceUserRequest;
use App\Modules\Attendance\Requests\UpdateAttendanceUserRequest;
use App\Modules\Attendance\Resources\AttendanceUserResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance Management
 * 
 * Endpoints for managing the mapping of employees to biometric attendance machine IDs.
 */
class AttendanceUserManagementController extends Controller
{
    use ApiResponses;

    /**
     * List all attendance user mappings.
     */
    public function index(AttendanceUserIndexRequest $request): JsonResponse
    {
        $mappings = AttendanceUser::with('employee')
            ->filter($request->validated())
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->successResponse(
            AttendanceUserResource::collection($mappings)->response()->getData(true),
            'Daftar pemetaan user absensi berhasil diambil.'
        );
    }

    /**
     * Store a new attendance user mapping.
     */
    public function store(StoreAttendanceUserRequest $request): JsonResponse
    {
        $mapping = AttendanceUser::create($request->validated());

        return $this->successResponse(
            new AttendanceUserResource($mapping->load('employee')),
            'Pemetaan user absensi berhasil dibuat.',
            201
        );
    }

    /**
     * Show a specific attendance user mapping.
     */
    public function show(AttendanceUser $attendanceUser): JsonResponse
    {
        return $this->successResponse(
            new AttendanceUserResource($attendanceUser->load('employee')),
            'Detail pemetaan user absensi berhasil diambil.'
        );
    }

    /**
     * Update an attendance user mapping.
     */
    public function update(UpdateAttendanceUserRequest $request, AttendanceUser $attendanceUser): JsonResponse
    {
        $attendanceUser->update($request->validated());

        return $this->successResponse(
            new AttendanceUserResource($attendanceUser->load('employee')),
            'Pemetaan user absensi berhasil diperbarui.'
        );
    }

    /**
     * Remove an attendance user mapping.
     */
    public function destroy(AttendanceUser $attendanceUser): JsonResponse
    {
        $attendanceUser->delete();

        return $this->successResponse(
            null,
            'Pemetaan user absensi berhasil dihapus.'
        );
    }
}
