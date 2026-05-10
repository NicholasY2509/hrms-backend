<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\AttendanceUser;
use App\Modules\Attendance\Requests\AttendanceUserIndexRequest;
use App\Modules\Attendance\Requests\StoreAttendanceUserRequest;
use App\Modules\Attendance\Requests\UpdateAttendanceUserRequest;
use App\Modules\Attendance\Resources\AttendanceUserResource;
use App\Modules\Attendance\Services\AttendanceUserService;
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

    protected $service;

    public function __construct(AttendanceUserService $service)
    {
        $this->service = $service;
    }

    /**
     * List all attendance user mappings.
     */
    public function index(AttendanceUserIndexRequest $request): JsonResponse
    {
        $mappings = $this->service->getPaginated(
            $request->validated(),
            $request->input('per_page', 15)
        );

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
        $mapping = $this->service->createMapping($request->validated());

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
            new AttendanceUserResource($attendanceUser->load(['employee', 'zktecoMachine'])),
            'Detail pemetaan user absensi berhasil diambil.'
        );
    }

    /**
     * Update an attendance user mapping.
     */
    public function update(UpdateAttendanceUserRequest $request, AttendanceUser $attendanceUser): JsonResponse
    {
        $mapping = $this->service->updateMapping($attendanceUser, $request->validated());

        return $this->successResponse(
            new AttendanceUserResource($mapping),
            'Pemetaan user absensi berhasil diperbarui.'
        );
    }

    /**
     * Remove an attendance user mapping.
     */
    public function destroy(AttendanceUser $attendanceUser): JsonResponse
    {
        $this->service->deleteMapping($attendanceUser);

        return $this->successResponse(
            null,
            'Pemetaan user absensi berhasil dihapus.'
        );
    }
}
