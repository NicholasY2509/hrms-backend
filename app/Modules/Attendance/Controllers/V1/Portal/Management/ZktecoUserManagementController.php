<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\ZktecoMachine;
use App\Modules\Attendance\Models\ZktecoUser;
use App\Modules\Attendance\Requests\ZktecoUserIndexRequest;
use App\Modules\Attendance\Requests\ZktecoUserSyncRequest;
use App\Modules\Attendance\Resources\ZktecoUserResource;
use App\Modules\Attendance\Services\ZktecoUserService;
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

    protected $service;

    public function __construct(ZktecoUserService $service)
    {
        $this->service = $service;
    }

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

    /**
     * Sync users from a ZKTeco machine.
     * 
     * This will trigger a background job to pull all users from the specified machine.
     * 
     * @bodyParam zkteco_machine_id int required The ID of the ZKTeco machine. Example: 1
     */
    public function sync(ZktecoUserSyncRequest $request): JsonResponse
    {
        $machine = ZktecoMachine::findOrFail($request->zkteco_machine_id);

        $task = $this->service->initiateSync($machine);

        return $this->successResponse(
            ['task_id' => $task->id],
            'Sinkronisasi user ZKTeco sedang diproses di latar belakang.'
        );
    }
}
