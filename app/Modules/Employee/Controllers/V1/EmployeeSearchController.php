<?php

namespace App\Modules\Employee\Controllers\V1;

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
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_id_number', 'like', "%{$search}%");
                });
            })
            ->limit(15)
            ->get();

        return $this->successResponse(
            EmployeeResource::collection($employees), 
            'Employees retrieved'
        );
    }
}
