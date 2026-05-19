<?php

namespace App\Modules\Payroll\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Services\EmployeeSalaryService;
use App\Modules\Payroll\Resources\V1\EmployeeSalaryResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Payroll Employee
 */
class MySalaryController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected EmployeeSalaryService $service
    ) {}

    /**
     * Get the authenticated user's current salary details.
     * 
     * @response {
     *  "status": "Success",
     *  "message": "Salary details retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "employee_id": 1,
     *      "bpjs_base_amount": 5000000,
     *      "actual_base_amount": 5000000,
     *      "hourly_rate": 28901.73,
     *      "real_hourly_rate": 28901.73,
     *      "currency": "IDR",
     *      "calculation_factor": 173,
     *      "effective_date": "2026-04-21",
     *      "is_active": true,
     *      "updated_at": "2026-04-21 10:00:00"
     *  }
     * }
     */
    public function index(): JsonResponse
    {
        $employeeId = Auth::user()?->user_employee?->employee_id;

        if (!$employeeId) {
            return $this->errorResponse('Employee record not found for this user.', 404);
        }

        $salary = $this->service->getActiveSalary($employeeId);

        if (!$salary) {
            return $this->errorResponse('Data gaji tidak ditemukan!', 404);
        }

        return $this->successResponse(
            new EmployeeSalaryResource($salary),
            'Salary details retrieved successfully'
        );
    }
}
