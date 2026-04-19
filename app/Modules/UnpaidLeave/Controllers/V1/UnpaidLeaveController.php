<?php

namespace App\Modules\UnpaidLeave\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\UnpaidLeave\Requests\V1\StoreUnpaidLeaveRequest;
use App\Modules\UnpaidLeave\Resources\V1\UnpaidLeaveResource;
use App\Modules\UnpaidLeave\Services\UnpaidLeaveService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnpaidLeaveController extends Controller
{
    use ApiResponses;

    private UnpaidLeaveService $service;

    public function __construct(UnpaidLeaveService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of personal unpaid leaves.
     * 
     * @group Unpaid Leave
     * @queryParam per_page int Results per page. Default: 15
     * @response 200 {
     *  "status": "Success",
     *  "message": "Unpaid leaves retrieved successfully.",
     *  "data": [
     *    {
     *      "id": 1,
     *      "employee": {"id": 1, "full_name": "John Doe"},
     *      "type": {"id": 1, "name": "Sickness"},
     *      "status": "Approved by Manager Name"
     *    }
     *  ],
     *  "links": {"first": "...", "last": "...", "prev": null, "next": null},
     *  "meta": {"current_page": 1, "total": 1}
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $employeeId = Auth::user()->user_employee?->employee_id;

        if (!$employeeId) {
            return $this->errorResponse('Employee record not found for this user.', 404);
        }

        $perPage = $request->query('per_page', 10);
        $leaves = $this->service->getUserUnpaidLeaves($employeeId, $perPage);

        return $this->successResponse(
            UnpaidLeaveResource::collection($leaves)->response()->getData(true),
            'Unpaid leaves retrieved successfully.'
        );
    }

    /**
     * Store a new unpaid leave request.
     * 
     * @group Unpaid Leave
     * @bodyParam unpaid_leave_type_id int required ID of the unpaid leave type.
     * @bodyParam start_date date required Start date of the leave.
     * @bodyParam end_date date required End date of the leave.
     * @bodyParam note string optional Reason for leave.
     * @bodyParam attachment file optional Proof of leave.
     */
    public function store(StoreUnpaidLeaveRequest $request): JsonResponse
    {
        $employeeId = Auth::user()->user_employee?->employee_id;

        if (!$employeeId) {
            return $this->errorResponse('Employee record not found for this user.', 404);
        }

        $data = $request->validated();
        $data['employee_id'] = $employeeId;

        $leave = $this->service->createUnpaidLeave($data, $request->file('attachment'));

        return $this->successResponse(
            new UnpaidLeaveResource($leave),
            'Unpaid leave request created successfully.',
            201
        );
    }

    /**
     * Display the specified unpaid leave.
     * 
     * @group Unpaid Leave
     * @response 200 {
     *  "status": "Success",
     *  "message": "Unpaid leave details retrieved successfully.",
     *  "data": {
     *      "id": 1,
     *      "employee": {"id": 1, "full_name": "John Doe"},
     *      "type": {"id": 1, "name": "Sickness"},
     *      "status": "Pending",
     *      "approvals": [
     *          {
     *              "id": 1,
     *              "approver_name": "Admin HRD",
     *              "role": "Admin HRD",
     *              "status": "Pending",
     *              "note": null,
     *              "updated_at": "2026-04-19 13:00:00"
     *          }
     *      ]
     *  }
     * }
     */
    public function show($id): JsonResponse
    {
        $employeeId = Auth::user()->user_employee?->employee_id;

        if (!$employeeId) {
            return $this->errorResponse('Employee record not found for this user.', 404);
        }

        $leave = $this->service->getUnpaidLeaveDetail($id, $employeeId);

        if (!$leave) {
            return $this->errorResponse('Unpaid leave not found or access denied.', 404);
        }

        return $this->successResponse(
            new UnpaidLeaveResource($leave),
            'Unpaid leave details retrieved successfully.'
        );
    }
}
