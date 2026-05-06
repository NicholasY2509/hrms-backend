<?php

namespace App\Modules\Attendance\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Attendance\Models\WorkingHour;
use App\Modules\Attendance\Requests\StoreWorkingHourRequest;
use App\Modules\Attendance\Requests\UpdateWorkingHourRequest;
use App\Modules\Attendance\Requests\WorkingHourIndexRequest;
use App\Modules\Attendance\Resources\WorkingHourResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Attendance Management
 * 
 * Endpoints for managing master data of working hours (shifts).
 */
class WorkingHourManagementController extends Controller
{
    use ApiResponses;

    /**
     * List all working hours.
     */
    public function index(WorkingHourIndexRequest $request): JsonResponse
    {
        $workingHours = WorkingHour::search($request->search)
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->successResponse(
            WorkingHourResource::collection($workingHours)->response()->getData(true),
            'Daftar jam kerja berhasil diambil.'
        );
    }

    /**
     * Store a new working hour.
     */
    public function store(StoreWorkingHourRequest $request): JsonResponse
    {
        $workingHour = WorkingHour::create($request->validated());

        return $this->successResponse(
            new WorkingHourResource($workingHour),
            'Jam kerja berhasil dibuat.',
            201
        );
    }

    /**
     * Show a specific working hour.
     */
    public function show(WorkingHour $workingHour): JsonResponse
    {
        return $this->successResponse(
            new WorkingHourResource($workingHour),
            'Detail jam kerja berhasil diambil.'
        );
    }

    /**
     * Update a working hour.
     */
    public function update(UpdateWorkingHourRequest $request, WorkingHour $workingHour): JsonResponse
    {
        $workingHour->update($request->validated());

        return $this->successResponse(
            new WorkingHourResource($workingHour),
            'Jam kerja berhasil diperbarui.'
        );
    }

    /**
     * Remove a working hour.
     */
    public function destroy(WorkingHour $workingHour): JsonResponse
    {
        $workingHour->delete();

        return $this->successResponse(
            null,
            'Jam kerja berhasil dihapus.'
        );
    }
}
