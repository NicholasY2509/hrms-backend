<?php

namespace App\Modules\Payroll\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Services\EmployeeSalaryComponentService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeSalaryComponentController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected EmployeeSalaryComponentService $service
    ) {}

    /**
     * @group Payroll Management
     * @queryParam employee_id int required
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['employee_id' => 'required|integer']);
        
        $components = $this->service->getEmployeeComponents((int) $request->query('employee_id'));
        return $this->successResponse($components, 'Employee salary components retrieved successfully');
    }

    /**
     * @group Payroll Management
     * @bodyParam employee_id int required
     * @bodyParam salary_component_id int required
     * @bodyParam amount float
     * @bodyParam is_calculated boolean
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'salary_component_id' => 'required|integer|exists:salary_components,id',
        ]);

        $assignment = $this->service->assignComponent(
            (int) $request->employee_id, 
            (int) $request->salary_component_id, 
            $request->all()
        );
        return $this->successResponse($assignment, 'Salary component assigned successfully', 201);
    }

    /**
     * @group Payroll Management
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->removeComponent($id);
        return $this->successResponse(null, 'Salary component assignment removed successfully');
    }
}
