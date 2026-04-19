<?php

namespace App\Modules\UnpaidLeave\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\UnpaidLeave\Resources\V1\UnpaidLeaveTypeResource;
use App\Modules\UnpaidLeave\Services\UnpaidLeaveTypeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

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
     * @response 200 {
     *  "status": "Success",
     *  "message": "Unpaid leave types retrieved successfully.",
     *  "data": [...]
     * }
     */
    public function index(): JsonResponse
    {
        $types = $this->service->getAllTypes();

        return $this->successResponse(
            UnpaidLeaveTypeResource::collection($types),
            'Unpaid leave types retrieved successfully.'
        );
    }
}
