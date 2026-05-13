<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\Employee;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @group Employee Management
 */
class EmployeeDetailController extends Controller
{
    use ApiResponses;

    /**
     * Get specific employee detail by type.
     * 
     * @urlParam id int required The ID of the employee.
     * @urlParam type string required The detail type (e.g., education, family, experiences, trainings, etc).
     */
    public function show(int $id, string $type): JsonResponse
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return $this->errorResponse('Employee not found', 404);
        }

        // Map frontend tab types to model relationships
        $typeMapping = [
            'education' => 'educations',
            'family' => 'families',
            'experience' => 'experiences',
            'training' => 'trainings',
            'performance' => 'performances',
            'inventory' => 'inventories',
            'bank' => 'banks',
            'warning' => 'warnings',
            'contract' => 'contracts',
            'emergency' => 'emergency_contacts',
            'license' => 'licenses',
            'vehicle' => 'vehicles',
            'attachment' => 'attachments',
            'social_security' => 'employee_bpjs',
            'insurance' => 'insurances',
        ];

        $relationship = $typeMapping[$type] ?? $type;

        if (!method_exists($employee, $relationship)) {
            $relationship = Str::plural($relationship);
            if (!method_exists($employee, $relationship)) {
                return $this->errorResponse("Detail type '{$type}' not supported.", 400);
            }
        }

        $data = $employee->{$relationship}()->get();

        return $this->successResponse($data, 'Data retrieved successfully');
    }
}
