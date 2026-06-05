<?php

namespace App\Modules\Leave\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Leave\Repositories\AnnualLeaveRepository;
use App\Modules\Leave\Requests\AnnualLeaveAdjustRequest;
use App\Modules\Leave\Requests\AnnualLeaveIndexRequest;
use App\Modules\Leave\Resources\AnnualLeaveResource;
use App\Modules\Leave\Services\AnnualLeaveService;
use App\Modules\Employee\Models\Employee;
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

    /**
     * Adjust Employee Annual Leave
     * 
     * Manually adjust the balance of annual_leave_2 and annual_leave_3 for an employee.
     * Records any discrepancies as addition/deduction logs.
     * 
     * @response {
     *  "success": true,
     *  "message": "Annual leave balance adjusted successfully",
     *  "data": null
     * }
     */
    public function adjust(AnnualLeaveAdjustRequest $request, Employee $employee): JsonResponse
    {
        $data = $request->validated();

        $this->service->adjustBalance(
            $employee,
            $data['annual_leave_2'],
            $data['annual_leave_3'],
            $data['keterangan']
        );

        return $this->successResponse(
            null,
            'Annual leave balance adjusted successfully'
        );
    }

    /**
     * Store Annual Leave Log
     * 
     * Store a new annual_leaves record directly without affecting the employee's current balances.
     * 
     * @response {
     *  "success": true,
     *  "message": "Annual leave log created successfully",
     *  "data": {
     *      "id": 1,
     *      "employee_id": 1,
     *      ...
     *  }
     * }
     */
    public function store(\App\Modules\Leave\Requests\AnnualLeaveStoreRequest $request): JsonResponse
    {
        $annualLeave = $this->service->recordOnly($request->validated());

        return $this->successResponse(
            new AnnualLeaveResource($annualLeave),
            'Annual leave log created successfully'
        );
    }

    /**
     * Annual Leave Summary Report
     * 
     * Retrieves a paginated list of employee annual leave summaries including initial balance, current balance, and totals.
     * 
     * @response {
     *  "success": true,
     *  "message": "Annual leave summary retrieved successfully",
     *  "data": [
     *      ...
     *  ]
     * }
     */
    public function summary(AnnualLeaveIndexRequest $request): JsonResponse
    {
        $summaries = $this->repository->getSummaryPaginated(
            $request->validated(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            \App\Modules\Leave\Resources\AnnualLeaveSummaryResource::collection($summaries)->response()->getData(true),
            'Annual leave summary retrieved successfully'
        );
    }

    /**
     * Annual Leave Summary Report for Employee
     * 
     * Retrieves the annual leave summary including initial balance, current balance, and totals for a single employee.
     * 
     * @response {
     *  "success": true,
     *  "message": "Employee annual leave summary retrieved successfully",
     *  "data": { ... }
     * }
     */
    public function employeeSummary(int $employee, \Illuminate\Http\Request $request): JsonResponse
    {
        $year = $request->input('year', date('Y'));
        
        $summary = $this->repository->getEmployeeSummary($employee, $year);

        return $this->successResponse(
            new \App\Modules\Leave\Resources\AnnualLeaveSummaryResource($summary),
            'Employee annual leave summary retrieved successfully'
        );
    }
}
