<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Management;

/**
 * @group Employee
 * @subgroup Management Portal
 */

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Resources\EmployeeResource;
use App\Traits\ApiResponses;
use App\Modules\Employee\Requests\ListEmployeeRequest;
use App\Modules\Employee\Requests\StoreEmployeeRequest;
use App\Modules\Employee\Requests\UpdateEmployeeRequest;
use App\Modules\Employee\Requests\GenerateNikRequest;
use App\Modules\Employee\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class EmployeeManagementController extends Controller
{
    use ApiResponses;

    protected EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * @group Employee
     * @subgroup Management
     * 
     * List all employees with search and pagination.
     */
    public function index(ListEmployeeRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $filters['work_employee_status_id'] = $request->input('work_employee_status_id', 1);
        
        $user = $request->user();
        $userRoles = (array) ($user->remote_roles ?? []);
        if (in_array('Department Head', $userRoles) && $user->employee) {
            $filters['department_id'] = $user->employee->department_id;
        }

        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);
        $cacheParams = compact('filters', 'perPage', 'page');
        ksort($cacheParams);
        $cacheKey = 'employees_management_index_' . md5(json_encode($cacheParams));

        $data = Cache::tags(['employees', 'management_index'])->remember($cacheKey, now()->addMinutes(30), function () use ($perPage, $filters, $page) {
            $employees = $this->employeeService->listEmployees($perPage, $filters, $page);
            $resource = EmployeeResource::collection($employees);
            return $resource->response()->getData(true);
        });

        return $this->successResponse(
            $data,
            'Employees retrieved'
        );
    }

    /**
     * @group Employee
     * @subgroup Management
     * 
     * Get summary of employees by status.
     */
    public function summary(): JsonResponse
    {
        $summary = $this->employeeService->getEmployeeSummary();

        return $this->successResponse($summary,
            'Employee summary retrieved'
        );
    }

    /**
     * @group Employee
     * @subgroup Management
     * 
     * Create a new employee.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->createEmployee($request->validated());

        return $this->successResponse(
            new EmployeeResource($employee),
            'Employee created successfully',
            201
        );
    }

    /**
     * @group Employee
     * @subgroup Management
     * 
     * Get employee details.
     */
    public function show(int $id): JsonResponse
    {
        $employee = $this->employeeService->getEmployee($id);

        return $this->successResponse(
            new EmployeeResource($employee),
            'Employee retrieved'
        );
    }

    /**
     * @group Employee
     * @subgroup Management
     * 
     * Update employee details.
     */
    public function update(UpdateEmployeeRequest $request, int $id): JsonResponse
    {
        $employee = $this->employeeService->updateEmployee($id, $request->validated());

        return $this->successResponse(
            new EmployeeResource($employee),
            'Employee updated successfully'
        );
    }

    /**
     * @group Employee
     * @subgroup Management
     * 
     * Delete an employee.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->employeeService->deleteEmployee($id);

        return $this->successResponse(
            null,
            'Employee deleted successfully'
        );
    }

    /**
     * @group Employee
     * @subgroup Management
     * 
     * Generate a new employee ID number (NIK) based on work position.
     * 
     * @queryParam work_position_id int required The ID of the work position. Example: 1
     */
    public function generateNik(GenerateNikRequest $request): JsonResponse
    {
        $nik = $this->employeeService->generateEmployeeIdNumber(
            $request->validated('work_position_id')
        );

        return $this->successResponse(
            ['employee_id_number' => $nik],
            'NIK generated successfully'
        );
    }
}