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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Employee
 * @subgroup Management
 */
class EmployeeSearchController extends Controller
{
    use ApiResponses;

    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->get('search');

        $employees = Employee::query()
            ->with(['position', 'department'])
            ->filter(['search' => $search])
            ->limit(15)
            ->get();

        return $this->successResponse(
            EmployeeResource::collection($employees), 
            'Employees retrieved'
        );
    }
}
