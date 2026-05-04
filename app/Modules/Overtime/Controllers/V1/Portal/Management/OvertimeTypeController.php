<?php

namespace App\Modules\Overtime\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Overtime\Requests\V1\StoreOvertimeTypeRequest;
use App\Modules\Overtime\Requests\V1\UpdateOvertimeTypeRequest;
use App\Modules\Overtime\Resources\V1\OvertimeTypeResource;
use App\Modules\Overtime\Services\OvertimeTypeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Overtime
 * @subgroup Management
 */
class OvertimeTypeController extends Controller
{
    use ApiResponses;

    private OvertimeTypeService $service;

    public function __construct(OvertimeTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of all overtime types.
     * 
     * @group Overtime
     */
    public function index(): JsonResponse
    {
        $types = $this->service->getAllTypes();

        return $this->successResponse(
            OvertimeTypeResource::collection($types),
            'Overtime types retrieved successfully.'
        );
    }

    /**
     * Store a newly created overtime type.
     * 
     * @group Overtime
     * @bodyParam name string required The name of the overtime type. Example: Regular Overtime
     * @bodyParam description string The description of the overtime type. Example: Standard overtime for weekdays.
     * @bodyParam price string required The price or formula code. Example: 1.5
     */
    public function store(StoreOvertimeTypeRequest $request): JsonResponse
    {
        $type = $this->service->createType($request->validated());

        return $this->successResponse(
            new OvertimeTypeResource($type),
            'Overtime type created successfully.',
            201
        );
    }

    /**
     * Display the specified overtime type.
     * 
     * @group Overtime
     */
    public function show(int $id): JsonResponse
    {
        $type = $this->service->getTypeById($id);

        if (!$type) {
            return $this->errorResponse('Overtime type not found.', 404);
        }

        return $this->successResponse(
            new OvertimeTypeResource($type),
            'Overtime type retrieved successfully.'
        );
    }

    /**
     * Update the specified overtime type.
     * 
     * @group Overtime
     * @bodyParam name string The name of the overtime type. Example: Regular Overtime
     * @bodyParam description string The description of the overtime type. Example: Standard overtime for weekdays.
     * @bodyParam price string The price or formula code. Example: 1.5
     */
    public function update(UpdateOvertimeTypeRequest $request, int $id): JsonResponse
    {
        try {
            $type = $this->service->updateType($id, $request->validated());

            return $this->successResponse(
                new OvertimeTypeResource($type),
                'Overtime type updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Remove the specified overtime type.
     * 
     * @group Overtime
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteType($id);

            return $this->successResponse(
                null,
                'Overtime type deleted successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
