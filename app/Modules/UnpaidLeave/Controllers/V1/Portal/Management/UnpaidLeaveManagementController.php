<?php

namespace App\Modules\UnpaidLeave\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\UnpaidLeave\Repositories\UnpaidLeaveRepository;
use App\Modules\UnpaidLeave\Resources\V1\UnpaidLeaveResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        protected UnpaidLeaveRepository $repository
    ) {}

    /**
     * List all employee unpaid leave requests.
     * 
     * @queryParam employee_id int Filter by employee.
     * @queryParam unpaid_leave_type_id int Filter by type.
     * @queryParam per_page int Results per page.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['employee_id', 'unpaid_leave_type_id']);
        $perPage = $request->query('per_page', 15);

        $leaves = $this->repository->paginate($filters, $perPage);

        return $this->successResponse(
            UnpaidLeaveResource::collection($leaves)->response()->getData(true),
            'All employee unpaid leave requests retrieved successfully.'
        );
    }

    /**
     * Show unpaid leave detail with approval progress.
     */
    public function show(int $id): JsonResponse
    {
        $leave = $this->repository->find($id);

        if (!$leave) {
            return $this->errorResponse('Unpaid leave request not found.', 404);
        }

        return $this->successResponse(
            new UnpaidLeaveResource($leave),
            'Unpaid leave detail retrieved.'
        );
    }
}
