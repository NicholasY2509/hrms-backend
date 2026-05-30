<?php

namespace App\Modules\Payroll\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Services\EmployeeSalaryService;
use App\Modules\Payroll\Resources\V1\EmployeeSalaryResource;
use App\Traits\ApiResponses;
use App\Modules\Payroll\Requests\EmployeeSalary\IndexEmployeeSalaryRequest;
use App\Modules\Payroll\Requests\EmployeeSalary\HistoryEmployeeSalaryRequest;
use App\Modules\Payroll\Requests\EmployeeSalary\StoreEmployeeSalaryRequest;
use App\Modules\Payroll\Requests\EmployeeSalary\UpdateEmployeeSalaryRequest;
use Illuminate\Http\JsonResponse;

class EmployeeSalaryController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected EmployeeSalaryService $service
    ) {}

    /**
     * @group Payroll Management
     */
    public function index(IndexEmployeeSalaryRequest $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $salaries = $this->service->getAllLatestSalariesPaginated($perPage, $search);

        return $this->successResponse(
            EmployeeSalaryResource::collection($salaries)->response()->getData(true),
            'Latest salaries retrieved successfully'
        );
    }

    /**
     * @group Payroll Management
     */
    public function history(HistoryEmployeeSalaryRequest $request): JsonResponse
    {
        $history = $this->service->getSalaryHistory($request->employee_id);
        return $this->successResponse(EmployeeSalaryResource::collection($history), 'Salary history retrieved successfully');
    }


    /**
     * @group Payroll Management
     */
    public function store(StoreEmployeeSalaryRequest $request): JsonResponse
    {
        $salary = $this->service->updateBaseSalary($request->employee_id, $request->validated());
        return $this->successResponse(new EmployeeSalaryResource($salary), 'Base salary updated successfully', 201);
    }

    /**
     * @group Payroll Management
     */
    public function show(int $id): JsonResponse
    {
        $salary = $this->service->getById($id);
        
        if (!$salary) {
            return $this->errorResponse('Employee salary not found', 404);
        }

        return $this->successResponse(new EmployeeSalaryResource($salary), 'Employee salary retrieved successfully');
    }

    /**
     * @group Payroll Management
     */
    public function update(UpdateEmployeeSalaryRequest $request, int $id): JsonResponse
    {
        $salary = $this->service->updateSalary($id, $request->validated());
        
        if (!$salary) {
            return $this->errorResponse('Employee salary not found', 404);
        }

        return $this->successResponse(new EmployeeSalaryResource($salary), 'Employee salary updated successfully');
    }

    /**
     * @group Payroll Management
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->deleteSalary($id);
        
        if (!$deleted) {
            return $this->errorResponse('Employee salary not found or could not be deleted', 404);
        }

        return $this->successResponse(null, 'Employee salary deleted successfully');
    }
}
