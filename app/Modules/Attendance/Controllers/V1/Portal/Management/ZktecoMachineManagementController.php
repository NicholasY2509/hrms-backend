<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\ZktecoMachine;
use App\Modules\Attendance\Requests\ZktecoMachineIndexRequest;
use App\Modules\Attendance\Requests\StoreZktecoMachineRequest;
use App\Modules\Attendance\Requests\UpdateZktecoMachineRequest;
use App\Modules\Attendance\Resources\ZktecoMachineResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance Management
 * 
 * Endpoints for managing ZKTeco biometric attendance machines.
 */
class ZktecoMachineManagementController extends Controller
{
    use ApiResponses;

    /**
     * List all ZKTeco machines.
     */
    public function index(ZktecoMachineIndexRequest $request): JsonResponse
    {
        $machines = ZktecoMachine::with(['work_location', 'attendance_location'])
            ->filter($request->validated())
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->successResponse(
            ZktecoMachineResource::collection($machines)->response()->getData(true),
            'Daftar mesin ZKTeco berhasil diambil.'
        );
    }

    /**
     * Store a new ZKTeco machine.
     */
    public function store(StoreZktecoMachineRequest $request): JsonResponse
    {
        $machine = ZktecoMachine::create($request->validated());

        return $this->successResponse(
            new ZktecoMachineResource($machine->load(['work_location', 'attendance_location'])),
            'Mesin ZKTeco berhasil dibuat.',
            201
        );
    }

    /**
     * Show a specific ZKTeco machine.
     */
    public function show(ZktecoMachine $zktecoMachine): JsonResponse
    {
        return $this->successResponse(
            new ZktecoMachineResource($zktecoMachine->load(['work_location', 'attendance_location'])),
            'Detail mesin ZKTeco berhasil diambil.'
        );
    }

    /**
     * Update a ZKTeco machine.
     */
    public function update(UpdateZktecoMachineRequest $request, ZktecoMachine $zktecoMachine): JsonResponse
    {
        $zktecoMachine->update($request->validated());

        return $this->successResponse(
            new ZktecoMachineResource($zktecoMachine->load(['work_location', 'attendance_location'])),
            'Mesin ZKTeco berhasil diperbarui.'
        );
    }

    /**
     * Remove a ZKTeco machine.
     */
    public function destroy(ZktecoMachine $zktecoMachine): JsonResponse
    {
        $zktecoMachine->delete();

        return $this->successResponse(
            null,
            'Mesin ZKTeco berhasil dihapus.'
        );
    }
}
