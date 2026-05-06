<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Resources\AttendanceMobileScanResource;
use App\Modules\Attendance\Services\AttendanceService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Attendance Management
 * 
 * Endpoints for managing and viewing mobile scans.
 */
class MobileScanManagementController extends Controller
{
    use ApiResponses;

    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * List all mobile scans.
     * 
     * @queryParam employee_id int Filter by employee ID.
     * @queryParam start_date date Filter by start date (YYYY-MM-DD).
     * @queryParam end_date date Filter by end date (YYYY-MM-DD).
     * @queryParam per_page int Results per page. Default: 15.
     */
    public function index(Request $request): JsonResponse
    {
        $scans = $this->attendanceService->getMobileScansPaginated(
            $request->all(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            AttendanceMobileScanResource::collection($scans)->response()->getData(true),
            'Daftar mobile scan berhasil diambil.'
        );
    }
}
