<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeOverviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'employee_id_number' => $this->employee_id_number,
            'initial' => $this->initial_name,
            'company_email' => $this->company_email,
            'position' => [
                'id' => $this->work_position_id,
                'name' => $this->position?->name,
            ],
            'department' => [
                'id' => $this->department_id,
                'name' => $this->department?->name,
            ],
            'work_location' => [
                'id' => $this->work_location_id,
                'name' => $this->work_location?->name,
            ],
            'work_employee_status' => [
                'id' => $this->work_employee_status_id,
                'name' => $this->work_employee_status?->name,
            ],
            'employee_status' => [
                'id' => $this->employee_status_id,
                'name' => $this->employee_status?->name,
            ],
            'team' => [
                'id' => $this->team_id,
                'name' => $this->team?->name,
            ],
            'supervisor' => $this->supervisor?->employee ? [
                'id' => $this->supervisor->employee->id,
                'name' => $this->supervisor->employee->full_name,
            ] : null,
            'annual_leave_2' => $this->annual_leave_2,
            'annual_leave_3' => $this->annual_leave_3,
        ];
    }
}
