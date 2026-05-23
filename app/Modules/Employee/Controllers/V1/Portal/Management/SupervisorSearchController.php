<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Management;

/**
 * @group Employee
 * @subgroup Management Portal
 */

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Resources\EmployeeResource;
use App\Modules\Organization\Models\PositionHierarchyMatrix;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupervisorSearchController extends Controller
{
    use ApiResponses;

    public function __invoke(Request $request): JsonResponse
    {
        $search = $request->get('search');

        // Get distinct supervisor position IDs from the matrix
        $supervisorPositionIds = PositionHierarchyMatrix::select('supervisor_work_position_id')
            ->distinct()
            ->pluck('supervisor_work_position_id');

        $employees = Employee::query()
            ->with(['position', 'department'])
            ->whereIn('work_position_id', $supervisorPositionIds)
            ->filter(['search' => $search])
            ->limit(15)
            ->get();

        return $this->successResponse(
            EmployeeResource::collection($employees), 
            'Supervisors retrieved'
        );
    }
}
