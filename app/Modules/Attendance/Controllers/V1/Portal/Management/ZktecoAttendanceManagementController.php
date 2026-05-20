<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\ZktecoAttendance;
use App\Modules\Attendance\Models\ZktecoMachine;
use App\Modules\Attendance\Requests\ZktecoAttendanceIndexRequest;
use App\Modules\Attendance\Requests\ZktecoAttendanceSyncRequest;
use App\Modules\Attendance\Resources\ZktecoAttendanceResource;
use App\Modules\Attendance\Services\ZktecoLogService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance Management
 *
 * Endpoints for viewing and syncing raw attendance logs from ZKTeco biometric machines.
 */
class ZktecoAttendanceManagementController extends Controller
{
    use ApiResponses;

    protected $service;

    public function __construct(ZktecoLogService $service)
    {
        $this->service = $service;
    }

    /**
     * List all ZKTeco attendance logs.
     */
    public function index(ZktecoAttendanceIndexRequest $request): JsonResponse
    {
        $logs = ZktecoAttendance::with(['zkteco_machine', 'attendance_user.employee'])
            ->filter($request->validated())
            ->orderBy('attendance_at', 'desc')
            ->orderBy('timestamp', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->successResponse(
            ZktecoAttendanceResource::collection($logs)->response()->getData(true),
            'Daftar log absensi ZKTeco berhasil diambil.'
        );
    }

    /**
     * Sync attendance logs from a ZKTeco machine.
     *
     * This will trigger a background job to pull all attendance logs for the specified range.
     *
     * @bodyParam zkteco_machine_id int required The ID of the ZKTeco machine. Example: 1
     * @bodyParam start_date string required The start date (YYYY-MM-DD). Example: 2026-05-19
     * @bodyParam end_date string required The end date (YYYY-MM-DD). Example: 2026-05-19
     */
    public function sync(ZktecoAttendanceSyncRequest $request): JsonResponse
    {
        $machine = ZktecoMachine::findOrFail($request->zkteco_machine_id);

        $task = $this->service->initiateSync(
            $machine,
            $request->start_date,
            $request->end_date
        );

        return $this->successResponse(
            ['task_id' => $task->id],
            'Sinkronisasi log absensi ZKTeco sedang diproses di latar belakang.'
        );
    }
}
