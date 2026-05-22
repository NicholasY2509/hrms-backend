<?php

namespace App\Modules\ShiftExchange\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use App\Modules\ShiftExchange\Services\ShiftExchangeService;
use App\Modules\ShiftExchange\Requests\V1\ShiftExchangeManagementIndexRequest;
use App\Modules\ShiftExchange\Resources\V1\ShiftExchangeResource;
use Illuminate\Http\JsonResponse;

class ShiftExchangeManagementController extends Controller
{
    use ApiResponses;

    protected ShiftExchangeService $shiftExchangeService;

    public function __construct(ShiftExchangeService $shiftExchangeService)
    {
        $this->shiftExchangeService = $shiftExchangeService;
    }

    /**
     * Get list of shift exchanges for management.
     *
     * @group Portal - Management - Shift Exchange
     * @queryParam per_page int The number of items per page.
     * @queryParam search string Search by employee name.
     * @queryParam employee_id int Filter by employee ID.
     * @queryParam start_date date Filter by start date.
     * @queryParam end_date date Filter by end date.
     * @queryParam is_settled boolean Filter by settled status.
     * 
     * @param ShiftExchangeManagementIndexRequest $request
     * @return JsonResponse
     */
    public function index(ShiftExchangeManagementIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        
        $shiftExchanges = $this->shiftExchangeService->getPaginatedForManagement($filters, $request->input('per_page', 15));
        
        $resource = ShiftExchangeResource::collection($shiftExchanges);
        
        return $this->successResponse(
            $resource->response()->getData(true),
            'Shift exchanges retrieved successfully'
        );
    }

    /**
     * Show a shift exchange detail.
     *
     * @group Portal - Management - Shift Exchange
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $shiftExchange = $this->shiftExchangeService->findOrFail($id);
        
        return $this->successResponse(
            new ShiftExchangeResource($shiftExchange),
            'Shift exchange detail retrieved successfully'
        );
    }

    /**
     * Settle a shift exchange request.
     *
     * @group Portal - Management - Shift Exchange
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function settle(int $id): JsonResponse
    {
        try {
            $shiftExchange = $this->shiftExchangeService->settle($id);
            
            return $this->successResponse(
                new ShiftExchangeResource($shiftExchange),
                'Shift exchange settled successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
