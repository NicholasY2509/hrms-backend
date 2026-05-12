<?php

namespace App\Modules\UnpaidLeave\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Leave\Repositories\AnnualLeaveRepository;
use App\Modules\Leave\Services\AnnualLeaveService;
use App\Modules\UnpaidLeave\Requests\V1\GetUnpaidLeaveManagementRequest;
use App\Modules\UnpaidLeave\Repositories\UnpaidLeaveRepository;
use App\Modules\UnpaidLeave\Resources\V1\UnpaidLeaveResource;
use App\Modules\UnpaidLeave\Services\UnpaidLeaveService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        protected UnpaidLeaveRepository $repository,
        protected UnpaidLeaveService $unpaidLeaveService,
        protected AnnualLeaveService $annualLeaveService,
        protected AnnualLeaveRepository $annualLeaveRepository
    ) {}

    /**
     * List all employee unpaid leave requests.
     */
    public function index(GetUnpaidLeaveManagementRequest $request): JsonResponse
    {
        $filters = $request->validated();
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
        $leave = $this->repository->find($id);

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
}
