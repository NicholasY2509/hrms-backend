<?php

namespace App\Modules\Employee\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\EmployeeStatus;
use App\Modules\Employee\Repositories\EmployeeStatusRepository;
use App\Modules\Employee\Requests\V1\EmployeeStatusRequest;
use App\Modules\Employee\Resources\V1\EmployeeStatusResource;
use App\Modules\Employee\Services\EmployeeStatusService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Employee
 * @subgroup Configuration
 * 
 * Endpoints for managing employee statuses (e.g., Permanent, Contract, Probation).
 */
class EmployeeStatusController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected EmployeeStatusRepository $repository,
        protected EmployeeStatusService $service
    ) {}

    /**
     * List employee statuses.
     * 
     * @queryParam search string Search by name.
     * @queryParam per_page int Results per page.
     */
    public function index(EmployeeStatusRequest $request): JsonResponse
    {
        $statuses = $this->repository->paginate(
            $request->only(['search']),
            $request->query('per_page', 15)
        );

        return $this->successResponse(
            EmployeeStatusResource::collection($statuses)->response()->getData(true),
            'Employee statuses retrieved successfully.'
        );
    }

    /**
     * Store a new employee status.
     * 
     * @bodyParam name string required The name of the status. Example: Permanent
     */
    public function store(EmployeeStatusRequest $request): JsonResponse
    {
        $status = $this->service->createStatus($request->validated());

        return $this->successResponse(
            new EmployeeStatusResource($status),
            'Employee status created successfully.',
            201
        );
    }

    /**
     * Display an employee status.
     */
    public function show(EmployeeStatus $employee_status): JsonResponse
    {
        return $this->successResponse(
            new EmployeeStatusResource($employee_status),
            'Employee status retrieved successfully.'
        );
    }

    /**
     * Update an employee status.
     * 
     * @bodyParam name string required The name of the status. Example: Contract (Updated)
     */
    public function update(EmployeeStatusRequest $request, EmployeeStatus $employee_status): JsonResponse
    {
        $updatedStatus = $this->service->updateStatus($employee_status, $request->validated());

        return $this->successResponse(
            new EmployeeStatusResource($updatedStatus),
            'Employee status updated successfully.'
        );
    }

    /**
     * Delete an employee status.
     */
    public function destroy(EmployeeStatus $employee_status): JsonResponse
    {
        $this->service->deleteStatus($employee_status);

        return $this->successResponse(null, 'Employee status deleted successfully.');
    }
}
