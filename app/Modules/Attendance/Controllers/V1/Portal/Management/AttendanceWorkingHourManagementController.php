<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Requests\AttendanceWorkingHourImportRequest;
use App\Modules\Attendance\Resources\AttendanceWorkingHourResource;
use App\Modules\Attendance\Services\AttendanceService;
use App\Modules\Attendance\Requests\AttendanceWorkingHourIndexRequest;
use App\Modules\Attendance\Requests\AttendanceWorkingHourUpdateRequest;
use App\Modules\Attendance\Models\AttendanceWorkingHour;
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

    /**
     * Import attendance working hours (schedules) from Excel.
     */
    public function import(AttendanceWorkingHourImportRequest $request): JsonResponse
    {
        $task = $this->attendanceService->importWorkingHours(
            $request->validated(),
            auth()->id()
        );

        return $this->successResponse([
                'task_id' => $task->id,
                'status' => $task->status
            ],
            'Proses import jadwal kerja telah dimulai di latar belakang.'
        );
    }

    /**
     * Update an attendance working hour (schedule).
     * 
     * @bodyParam working_hour_id int required The ID of the working hour (shift). Example: 1
     * @bodyParam attendance_at date required The date of the attendance. Example: 2024-01-01
     */
    public function update(AttendanceWorkingHourUpdateRequest $request, AttendanceWorkingHour $attendanceWorkingHour): JsonResponse
    {
        $updated = $this->attendanceService->updateWorkingHour(
            $attendanceWorkingHour,
            $request->validated()
        );

        return $this->successResponse(
            new AttendanceWorkingHourResource($updated),
            'Jadwal kerja berhasil diperbarui.'
        );
    }
}
