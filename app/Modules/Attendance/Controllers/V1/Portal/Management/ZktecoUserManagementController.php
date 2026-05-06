<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\ZktecoUser;
use App\Modules\Attendance\Requests\ZktecoUserIndexRequest;
use App\Modules\Attendance\Resources\ZktecoUserResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance Management
 * 
 * Endpoints for viewing raw user data synced from ZKTeco biometric machines.
 */
class ZktecoUserManagementController extends Controller
{
    use ApiResponses;

    /**
     * List all ZKTeco machine users.
     */
    public function index(ZktecoUserIndexRequest $request): JsonResponse
    {
        $users = ZktecoUser::with(['machine', 'attendance_user.employee'])
            ->filter($request->validated())
            ->orderBy('name', 'asc')
            ->paginate($request->input('per_page', 15));

        return $this->successResponse(
            ZktecoUserResource::collection($users)->response()->getData(true),
            'Daftar user ZKTeco berhasil diambil.'
        );
    }
}
