<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\ZktecoAttendance;
use App\Modules\Attendance\Requests\ZktecoAttendanceIndexRequest;
use App\Modules\Attendance\Resources\ZktecoAttendanceResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance Management
 * 
 * Endpoints for viewing raw attendance logs from ZKTeco biometric machines.
 */
class ZktecoAttendanceManagementController extends Controller
{
    use ApiResponses;

    /**
     * List all ZKTeco attendance logs.
     */
    public function index(ZktecoAttendanceIndexRequest $request): JsonResponse
    {
        $logs = ZktecoAttendance::with(['machine', 'attendance_user.employee'])
            ->filter($request->validated())
            ->orderBy('attendance_at', 'desc')
            ->orderBy('timestamp', 'desc')
            ->paginate($request->input('per_page', 15));

        return $this->successResponse(
            ZktecoAttendanceResource::collection($logs)->response()->getData(true),
            'Daftar log absensi ZKTeco berhasil diambil.'
        );
    }
}
