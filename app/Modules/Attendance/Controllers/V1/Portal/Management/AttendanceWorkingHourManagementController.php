<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Resources\AttendanceWorkingHourResource;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Attendance\Requests\AttendanceWorkingHourIndexRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance Management
 * 
 * Endpoints for managing and viewing attendance working hours (schedules).
 */
class AttendanceWorkingHourManagementController extends Controller
{
    use ApiResponses;

    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * List all attendance working hours (schedules).
     */
    public function index(AttendanceWorkingHourIndexRequest $request): JsonResponse
    {
        $schedules = $this->attendanceService->getWorkingHoursPaginated(
            $request->validated(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            AttendanceWorkingHourResource::collection($schedules)->response()->getData(true),
            'Daftar jadwal kerja berhasil diambil.'
        );
    }
}
