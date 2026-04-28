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
     * Get all departments.
     */
    public function index(DepartmentIndexRequest $request): JsonResponse
    {
        $departments = $this->repository->getPaginated(
            $request->only('search'),
            $request->input('per_page', 15)
        );

        $resource = DepartmentResource::collection($departments);

        return $this->successResponse(
            $resource->response()->getData(true),
            'Departments retrieved successfully'
        );
    }

    /**
     * Store a new department.
     * 
     * @bodyParam name string required The name of the department.
     * @bodyParam dept_head_id int The ID of the employee who heads the department.
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
     * Get department details.
     */
    public function show(int $id): JsonResponse
    {
        $department = $this->repository->findById($id);

        if (!$department) {
            return $this->errorResponse('Department not found', 404);
        }

        return $this->successResponse(
            new DepartmentResource($department),
            'Department details retrieved'
        );
    }

    /**
     * Update a department.
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
     * Delete a department.
     */
    public function destroy(Department $department): JsonResponse
    {
        $this->service->deleteDepartment($department);

        return $this->successResponse(null, 'Department deleted successfully');
    }
}
