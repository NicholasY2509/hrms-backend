<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Services\EmployeeTaxProfileService;
use App\Modules\Employee\Resources\V1\EmployeeTaxProfileResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeTaxProfileController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected EmployeeTaxProfileService $service
    ) {}

    /**
     * @group Employee Management
     */
    public function show(int $employeeId): JsonResponse
    {
        $profile = $this->service->getTaxProfile($employeeId);
        return $this->successResponse(
            $profile ? new EmployeeTaxProfileResource($profile) : null, 
            'Tax profile retrieved successfully'
        );
    }

    /**
     * @group Employee Management
     */
    public function store(Request $request): JsonResponse
    {
        $profile = $this->service->updateTaxProfile($request->employee_id, $request->all());
        return $this->successResponse(new EmployeeTaxProfileResource($profile), 'Tax profile updated successfully', 201);
    }
}
