<?php

namespace App\Modules\Payroll\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Services\EmployeeSalaryService;
use App\Modules\Payroll\Resources\V1\EmployeeSalaryResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeSalaryController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected EmployeeSalaryService $service
    ) {}

    /**
     * @group Payroll Management
     * @queryParam employee_id int required
     */
    public function index(Request $request): JsonResponse
    {
        $history = $this->service->getSalaryHistory($request->employee_id);
        return $this->successResponse(EmployeeSalaryResource::collection($history), 'Salary history retrieved successfully');
    }

    /**
     * @group Payroll Management
     * @bodyParam employee_id int required
     * @bodyParam bpjs_base_amount float required
     * @bodyParam actual_base_amount float required
     * @bodyParam effective_date date required
     */
    public function store(Request $request): JsonResponse
    {
        $salary = $this->service->updateBaseSalary($request->employee_id, $request->all());
        return $this->successResponse(new EmployeeSalaryResource($salary), 'Base salary updated successfully', 201);
    }
}
