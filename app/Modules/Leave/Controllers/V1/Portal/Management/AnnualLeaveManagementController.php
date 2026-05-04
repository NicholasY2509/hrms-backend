<?php

namespace App\Modules\Leave\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Leave\Repositories\AnnualLeaveRepository;
use App\Modules\Leave\Requests\AnnualLeaveIndexRequest;
use App\Modules\Leave\Resources\AnnualLeaveResource;
use App\Modules\Leave\Services\AnnualLeaveService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Leave
 * @subgroup Management Portal
 */
class AnnualLeaveManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected AnnualLeaveService $service,
        protected AnnualLeaveRepository $repository
    ) {}

    /**
     * List Annual Leaves
     * 
     * Retrieves a paginated list of annual leave deduction records for all employees.
     * 
     * @response {
     *  "success": true,
     *  "message": "Annual leaves retrieved successfully",
     *  "data": [
     *    {
     *      "id": 1,
     *      "employee_id": 1,
     *      "employee": {
     *        "id": 1,
     *        "name": "John Doe",
     *        "nik": "123456789"
     *      },
     *      "annual_leave_at": "2024-05-01",
     *      "total": 1,
     *      "status": "APPROVED",
     *      "description": "Annual Leave deduction",
     *      "deduction_details": [
     *        {
     *          "year": 2024,
     *          "amount": 1
     *        }
     *      ],
     *      "created_at": "2024-05-01T00:00:00.000000Z",
     *      "updated_at": "2024-05-01T00:00:00.000000Z"
     *    }
     *  ],
     *  "links": {
     *    "first": "...",
     *    "last": "...",
     *    "prev": null,
     *    "next": "..."
     *  },
     *  "meta": {
     *    "current_page": 1,
     *    "from": 1,
     *    "last_page": 1,
     *    "path": "...",
     *    "per_page": 15,
     *    "to": 1,
     *    "total": 1
     *  }
     * }
     */
    public function index(AnnualLeaveIndexRequest $request): JsonResponse
    {
        $annualLeaves = $this->repository->getPaginated(
            $request->validated(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            AnnualLeaveResource::collection($annualLeaves)->response()->getData(true),
            'Annual leaves retrieved successfully'
        );
    }
}
