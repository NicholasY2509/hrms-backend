<?php

namespace App\Modules\Payroll\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Services\PayrollService;
use App\Modules\Payroll\Resources\V1\SalaryResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Payroll
 */
class PayrollController extends Controller
{
    use ApiResponses;

    protected PayrollService $service;

    public function __construct(PayrollService $service)
    {
        $this->service = $service;
    }

    /**
     * Get the authenticated user's salary details.
     * 
     * @response {
     *  "status": "Success",
     *  "message": "Salary details retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "employee_id": 1,
     *      "basic_salary": 5000000,
     *      "hourly_rate": 28901.73,
     *      "currency": "IDR",
     *      "calculation_factor": 173,
     *      "updated_at": "2026-04-21 10:00:00"
     *  }
     * }
     */
    public function salaryDetails(): JsonResponse
    {
        $employeeId = Auth::user()->user_employee?->employee_id;

        if (!$employeeId) {
            return $this->errorResponse('Employee record not found for this user.', 404);
        }

        $salary = $this->service->getActiveSalary($employeeId);

        if (!$salary) {
            return $this->errorResponse('Data gaji tidak ditemukan!', 404);
        }

        return $this->successResponse(
            new SalaryResource($salary),
            'Salary details retrieved successfully'
        );
    }
}
