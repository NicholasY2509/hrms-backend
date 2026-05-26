<?php

namespace App\Modules\UnpaidLeave\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\UnpaidLeave\Requests\V1\GetUnpaidLeaveManagementRequest;
use App\Modules\UnpaidLeave\Requests\V1\StoreUnpaidLeaveManagementRequest;
use App\Modules\UnpaidLeave\Requests\V1\GetUnpaidLeaveCalendarRequest;
use App\Modules\UnpaidLeave\Resources\V1\UnpaidLeaveResource;
use App\Modules\UnpaidLeave\Resources\V1\UnpaidLeaveCalendarResource;
use App\Modules\UnpaidLeave\Resources\V1\HolidayResource;
use App\Modules\UnpaidLeave\Services\UnpaidLeaveService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Unpaid Leave
 * @subgroup Management Portal
 * 
 * Endpoints for HR/Management to monitor all employee unpaid leave requests.
 */
class UnpaidLeaveManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected UnpaidLeaveService $unpaidLeaveService
    ) {}

    /**
     * List all employee unpaid leave requests.
     */
    public function index(GetUnpaidLeaveManagementRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $request->query('per_page', 15);

        $leaves = $this->unpaidLeaveService->getPaginatedLeaves($filters, $perPage);

        return $this->successResponse(
            UnpaidLeaveResource::collection($leaves)->response()->getData(true),
            'All employee unpaid leave requests retrieved successfully.'
        );
    }

    /**
     * Create an unpaid leave request for an employee.
     * 
     * @response 201 {
     *  "status": "Success",
     *  "message": "Unpaid leave request created successfully.",
     *  "data": {...}
     * }
     */
    public function store(StoreUnpaidLeaveManagementRequest $request): JsonResponse
    {
        $data = $request->validated();

        $leave = $this->unpaidLeaveService->createUnpaidLeave($data, $request->file('attachment'));

        return $this->successResponse(
            new UnpaidLeaveResource($leave),
            'Unpaid leave request created successfully.',
            201
        );
    }

    /**
     * Show unpaid leave detail with approval progress.
     */
    public function show(int $id): JsonResponse
    {
        $leave = $this->unpaidLeaveService->getLeaveDetail($id);

        if (!$leave) {
            return $this->errorResponse('Unpaid leave request not found.', 404);
        }

        return $this->successResponse(
            new UnpaidLeaveResource($leave),
            'Unpaid leave detail retrieved.'
        );
    }

    /**
     * Settle an unpaid leave request.
     * 
     * @response 200 {
     *  "status": "Success",
     *  "message": "Unpaid leave settled successfully.",
     *  "data": {...}
     * }
     */
    public function settle(int $id): JsonResponse
    {
        $leave = $this->unpaidLeaveService->getLeaveDetail($id);

        if (!$leave) {
            return $this->errorResponse('Unpaid leave request not found.', 404);
        }

        if ($leave->settled_at) {
            return $this->errorResponse('Unpaid leave request is already settled.', 400);
        }

        try {
            $leave = $this->unpaidLeaveService->settle($leave);

            return $this->successResponse(
                new UnpaidLeaveResource($leave),
                'Unpaid leave request settled successfully.'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to settle unpaid leave: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get unpaid leave data for calendar view.
     * 
     * This endpoint returns a combined list of unpaid leave requests and public holidays 
     * within the specified date range.
     */
    public function calendar(GetUnpaidLeaveCalendarRequest $request): JsonResponse
    {
        $filters = $request->validated();
        
        $data = $this->unpaidLeaveService->getCalendarData($filters);

        return $this->successResponse([
            'leaves' => UnpaidLeaveCalendarResource::collection($data['leaves']),
            'holidays' => HolidayResource::collection($data['holidays']),
        ], 'Calendar data retrieved successfully.');
    }
}
