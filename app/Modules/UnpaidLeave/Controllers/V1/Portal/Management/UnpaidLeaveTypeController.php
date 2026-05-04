<?php

namespace App\Modules\UnpaidLeave\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\UnpaidLeave\Requests\V1\StoreUnpaidLeaveTypeRequest;
use App\Modules\UnpaidLeave\Requests\V1\UpdateUnpaidLeaveTypeRequest;
use App\Modules\UnpaidLeave\Resources\V1\UnpaidLeaveTypeResource;
use App\Modules\UnpaidLeave\Services\UnpaidLeaveTypeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Unpaid Leave
 * @subgroup Management
 */
class UnpaidLeaveTypeController extends Controller
{
    use ApiResponses;

    private UnpaidLeaveTypeService $service;

    public function __construct(UnpaidLeaveTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of all unpaid leave types.
     * 
     * @group Unpaid Leave
     */
    public function index(): JsonResponse
    {
        $types = $this->service->getAllTypes();

        return $this->successResponse(
            UnpaidLeaveTypeResource::collection($types),
            'Unpaid leave types retrieved successfully.'
        );
    }

    /**
     * Store a newly created unpaid leave type.
     * 
     * @group Unpaid Leave
     * @bodyParam name string required The name of the unpaid leave type. Example: Umroh
     * @bodyParam background_color string The background color for the badge. Example: #000000
     * @bodyParam border_color string The border color for the badge. Example: #000000
     * @bodyParam text_color string The text color for the badge. Example: #ffffff
     * @bodyParam limit integer The limit of days. Example: 10
     * @bodyParam is_annual_leave_deduction boolean required Whether this leave type deducts annual leave. Example: true
     */
    public function store(StoreUnpaidLeaveTypeRequest $request): JsonResponse
    {
        $type = $this->service->createType($request->validated());

        return $this->successResponse(
            new UnpaidLeaveTypeResource($type),
            'Unpaid leave type created successfully.',
            201
        );
    }

    /**
     * Display the specified unpaid leave type.
     * 
     * @group Unpaid Leave
     */
    public function show(int $id): JsonResponse
    {
        $type = $this->service->getTypeById($id);

        if (!$type) {
            return $this->errorResponse('Unpaid leave type not found.', 404);
        }

        return $this->successResponse(
            new UnpaidLeaveTypeResource($type),
            'Unpaid leave type retrieved successfully.'
        );
    }

    /**
     * Update the specified unpaid leave type.
     * 
     * @group Unpaid Leave
     * @bodyParam name string The name of the unpaid leave type. Example: Umroh
     * @bodyParam background_color string The background color for the badge. Example: #000000
     * @bodyParam border_color string The border color for the badge. Example: #000000
     * @bodyParam text_color string The text color for the badge. Example: #ffffff
     * @bodyParam limit integer The limit of days. Example: 10
     * @bodyParam is_annual_leave_deduction boolean Whether this leave type deducts annual leave. Example: true
     */
    public function update(UpdateUnpaidLeaveTypeRequest $request, int $id): JsonResponse
    {
        try {
            $type = $this->service->updateType($id, $request->validated());

            return $this->successResponse(
                new UnpaidLeaveTypeResource($type),
                'Unpaid leave type updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Remove the specified unpaid leave type.
     * 
     * @group Unpaid Leave
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteType($id);

            return $this->successResponse(
                null,
                'Unpaid leave type deleted successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
