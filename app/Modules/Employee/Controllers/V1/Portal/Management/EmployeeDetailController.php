<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\Employee;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Modules\Employee\Resources\EmployeeOverviewResource;
use App\Modules\Employee\Resources\EmployeePersonalResource;
use App\Modules\Employee\Resources\EmployeeEducationResource;
use App\Modules\Employee\Resources\EmployeeFamilyResource;
use App\Modules\Employee\Resources\EmployeeExperienceResource;
use App\Modules\Employee\Resources\EmployeeTrainingResource;
use App\Modules\Employee\Resources\EmployeePerformanceResource;
use App\Modules\Employee\Resources\EmployeeInventoryResource;
use App\Modules\Employee\Resources\EmployeeBankResource;
use App\Modules\Employee\Resources\EmployeeWarningResource;
use App\Modules\Employee\Resources\EmployeeContractResource;
use App\Modules\Employee\Resources\EmployeeEmergencyContactResource;
use App\Modules\Employee\Resources\EmployeeLicenseResource;
use App\Modules\Employee\Resources\EmployeeVehicleResource;
use App\Modules\Employee\Resources\EmployeeAttachmentResource;
use App\Modules\Employee\Resources\EmployeeInsuranceResource;
use App\Modules\Employee\Resources\V1\EmployeeTaxProfileResource;
use App\Modules\Employee\Requests\UpdateEmployeeDetailRequest;
use App\Modules\Employee\Resources\EmployeeAttendanceUserResource;
use App\Modules\Employee\Services\EmployeeService;

/**
 * @group Employee Management
 */
class EmployeeDetailController extends Controller
{
    use ApiResponses;

    protected EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

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
        
        $typeConfigs = $this->getTypeConfigs();

        if (!isset($typeConfigs[$type])) {
            return $this->errorResponse("Detail type '{$type}' not supported.", 400);
        }

        $config = $typeConfigs[$type];
        $resourceClass = $config['resource'];

        // Conditionally load relationships based on type
        if ($type === 'overview') {
            $employee->load([
                'position',
                'department',
                'work_location',
                'work_employee_status',
                'employee_status',
                'team',
                'supervisor.employee'
            ]);
        } elseif ($type === 'personal') {
            $employee->load([
                'gender',
                'religion',
                'marital_status',
                'blood_group'
            ]);
        } elseif ($type === 'insurance') {
            $employee->load('insurances');
        } elseif (isset($config['relation'])) {
            if ($config['relation'] === 'families') {
                $employee->load(['families.relationship', 'families.gender']);
            } else {
                $employee->load($config['relation']);
            }
        }

        if (isset($config['is_relation']) && $config['is_relation'] === false) {
            return $this->successResponse(
                new $resourceClass($employee),
                Str::title($type) . ' data retrieved'
            );
        }

        $relationship = $config['relation'];
        if (!method_exists($employee, $relationship)) {
            return $this->errorResponse("Relationship '{$relationship}' not found on Employee model.", 500);
        }

        if (isset($config['is_single']) && $config['is_single'] === true) {
            $data = $employee->{$relationship};
            return $this->successResponse(
                $data ? new $resourceClass($data) : null,
                Str::title($type) . ' data retrieved'
            );
        }

        $data = $employee->{$relationship};

        return $this->successResponse(
            $resourceClass::collection($data),
            Str::title($type) . ' data retrieved'
        );
    }

    /**
     * Update specific employee detail by type.
     * 
     * @urlParam id int required The ID of the employee.
     * @urlParam type string required The detail type.
     */
    public function update(UpdateEmployeeDetailRequest $request, int $id, string $type): JsonResponse
    {
        $typeConfigs = $this->getTypeConfigs();

        if (!isset($typeConfigs[$type])) {
            return $this->errorResponse("Detail type '{$type}' not supported.", 400);
        }

        $employee = $this->employeeService->updateDetail(
            $id, 
            $type, 
            $request->validated(), 
            $typeConfigs[$type]
        );

        return $this->show($id, $type);
    }

    /**
     * Get the type configurations mapping.
     */
    private function getTypeConfigs(): array
    {
        return [
            'overview' => [
                'resource' => EmployeeOverviewResource::class,
                'is_relation' => false
            ],
            'attendance-user' => [
                'resource' => EmployeeAttendanceUserResource::class,
                'relation' => 'attendance_users'
            ],
            'personal' => [
                'resource' => EmployeePersonalResource::class,
                'is_relation' => false
            ],
            'education' => [
                'resource' => EmployeeEducationResource::class,
                'relation' => 'educations'
            ],
            'family' => [
                'resource' => EmployeeFamilyResource::class,
                'relation' => 'families'
            ],
            'experience' => [
                'resource' => EmployeeExperienceResource::class,
                'relation' => 'experiences'
            ],
            'training' => [
                'resource' => EmployeeTrainingResource::class,
                'relation' => 'trainings'
            ],
            'performance' => [
                'resource' => EmployeePerformanceResource::class,
                'relation' => 'performances'
            ],
            'inventory' => [
                'resource' => EmployeeInventoryResource::class,
                'relation' => 'inventories'
            ],
            'bank' => [
                'resource' => EmployeeBankResource::class,
                'relation' => 'banks'
            ],
            'warning' => [
                'resource' => EmployeeWarningResource::class,
                'relation' => 'warnings'
            ],
            'contract' => [
                'resource' => EmployeeContractResource::class,
                'relation' => 'contracts'
            ],
            'emergency' => [
                'resource' => EmployeeEmergencyContactResource::class,
                'relation' => 'emergency_contacts'
            ],
            'license' => [
                'resource' => EmployeeLicenseResource::class,
                'relation' => 'licenses'
            ],
            'vehicle' => [
                'resource' => EmployeeVehicleResource::class,
                'relation' => 'vehicles'
            ],
            'attachment' => [
                'resource' => EmployeeAttachmentResource::class,
                'relation' => 'attachments'
            ],
            // 'social_security' => [
            //     'resource' => EmployeeBpjsResource::class,
            //     'relation' => 'employee_bpjs'
            // ],
            'insurance' => [
                'resource' => EmployeeInsuranceResource::class,
                'is_relation' => false
            ],
            'tax-profile' => [
                'resource' => EmployeeTaxProfileResource::class,
                'relation' => 'tax_profile',
                'is_single' => true
            ],
        ];
    }
}
