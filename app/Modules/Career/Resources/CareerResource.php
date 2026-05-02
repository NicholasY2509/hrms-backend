<?php

namespace App\Modules\Career\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->full_name,
                    'nik' => $this->employee->employee_id_number,
                ];
            }),
            'career_type_id' => $this->career_type_id,
            'career_type' => $this->whenLoaded('careerType', function () {
                return [
                    'id' => $this->careerType->id,
                    'name' => $this->careerType->name,
                ];
            }),
            
            'before_employee_status_id' => $this->before_employee_status_id,
            'before_work_position_id' => $this->before_work_position_id,
            'before_work_position' => $this->whenLoaded('beforeWorkPosition', function () {
                return [
                    'id' => $this->beforeWorkPosition->id,
                    'name' => $this->beforeWorkPosition->name,
                ];
            }),
            'before_department_id' => $this->before_department_id,
            'before_department' => $this->whenLoaded('beforeDepartment', function () {
                return [
                    'id' => $this->beforeDepartment->id,
                    'name' => $this->beforeDepartment->name,
                ];
            }),
            'before_work_location_id' => $this->before_work_location_id,
            'before_team_id' => $this->before_team_id,
            'before_team' => $this->whenLoaded('beforeTeam', function () {
                return [
                    'id' => $this->beforeTeam->id,
                    'name' => $this->beforeTeam->name,
                ];
            }),
            'before_supervisor_id' => $this->before_supervisor_id,
            
            'after_employee_status_id' => $this->after_employee_status_id,
            'after_work_position_id' => $this->after_work_position_id,
            'after_work_position' => $this->whenLoaded('afterWorkPosition', function () {
                return [
                    'id' => $this->afterWorkPosition->id,
                    'name' => $this->afterWorkPosition->name,
                ];
            }),
            'after_department_id' => $this->after_department_id,
            'after_department' => $this->whenLoaded('afterDepartment', function () {
                return [
                    'id' => $this->afterDepartment->id,
                    'name' => $this->afterDepartment->name,
                ];
            }),
            'after_work_location_id' => $this->after_work_location_id,
            'after_team_id' => $this->after_team_id,
            'after_team' => $this->whenLoaded('afterTeam', function () {
                return [
                    'id' => $this->afterTeam->id,
                    'name' => $this->afterTeam->name,
                ];
            }),
            'after_supervisor_id' => $this->after_supervisor_id,
            
            'career_at' => $this->career_at,
            'note' => $this->note,
            'confirmed_at' => $this->confirmed_at,
            'settled_at' => $this->settled_at,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
