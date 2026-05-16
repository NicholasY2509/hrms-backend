<?php

namespace App\Modules\Payroll\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Services\SalaryComponentService;
use App\Modules\Payroll\Resources\V1\SalaryComponentResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalaryComponentController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected SalaryComponentService $service
    ) {}

    /**
     * @group Payroll Configuration
     */
    public function index(): JsonResponse
    {
        $components = $this->service->getAllComponents();
        return $this->successResponse(SalaryComponentResource::collection($components), 'Salary components retrieved successfully');
    }

    /**
     * @group Payroll Configuration
     * @bodyParam name string required Example: Meal Allowance
     * @bodyParam code string required Example: MEAL_ALW
     * @bodyParam category string required enum:allowance,deduction,benefit
     */
    public function store(Request $request): JsonResponse
    {
        $component = $this->service->createComponent($request->all());
        return $this->successResponse(new SalaryComponentResource($component), 'Salary component created successfully', 201);
    }

    /**
     * @group Payroll Configuration
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->service->updateComponent($id, $request->all());
        return $this->successResponse(null, 'Salary component updated successfully');
    }

    /**
     * @group Payroll Configuration
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteComponent($id);
        return $this->successResponse(null, 'Salary component deleted successfully');
    }
}
