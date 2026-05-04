<?php

namespace App\Modules\Organization\Controllers\V1\Portal\Management;

/**
 * @group Organization
 * @subgroup Management Portal
 */

use App\Http\Controllers\Controller;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Repositories\DepartmentRepository;
use App\Modules\Organization\Requests\DepartmentIndexRequest;
use App\Modules\Organization\Requests\DepartmentRequest;
use App\Modules\Organization\Resources\DepartmentResource;
use App\Modules\Organization\Services\DepartmentService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Organization
 * @subgroup Department
 */
class DepartmentController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected DepartmentRepository $repository,
        protected DepartmentService $service
    ) {}

    /**
     * List departments.
     * 
     * Get a paginated list of departments with optional search.
     */
    public function index(DepartmentIndexRequest $request): JsonResponse
    {
        $departments = $this->repository->getPaginated(
            $request->only('search'),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            DepartmentResource::collection($departments)->response()->getData(true),
            'Departments retrieved successfully'
        );
    }

    /**
     * Create department.
     * 
     * Store a new department in the system.
     */
    public function store(DepartmentRequest $request): JsonResponse
    {
        $department = $this->service->createDepartment($request->validated());

        return $this->successResponse(
            new DepartmentResource($department),
            'Department created successfully',
            201
        );
    }

    /**
     * Get department.
     * 
     * Get detailed information about a specific department.
     */
    public function show(Department $department): JsonResponse
    {
        return $this->successResponse(
            new DepartmentResource($department->load(['heads.employee', 'heads.workLocation'])),
            'Department details retrieved'
        );
    }

    /**
     * Update department.
     * 
     * Update the details of an existing department.
     */
    public function update(DepartmentRequest $request, Department $department): JsonResponse
    {
        $updatedDepartment = $this->service->updateDepartment($department, $request->validated());

        return $this->successResponse(
            new DepartmentResource($updatedDepartment),
            'Department updated successfully'
        );
    }

    /**
     * Delete department.
     * 
     * Remove a department from the system.
     */
    public function destroy(Department $department): JsonResponse
    {
        $this->service->deleteDepartment($department);

        return $this->successResponse(null, 'Department deleted successfully');
    }
}
