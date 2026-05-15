<?php

namespace App\Modules\Career\Resources;

use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestStepResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->full_name,
                    'nik' => $this->employee->employee_id_number,
                ];
            }),
            'career_type' => $this->whenLoaded('careerType', function () {
                return [
                    'id' => $this->careerType->id,
                    'name' => $this->careerType->name,
                ];
            }),
            'before_employee_status' => $this->whenLoaded('beforeEmployeeStatus', function () {
                return [
                    'id' => $this->beforeEmployeeStatus->id,
                    'name' => $this->beforeEmployeeStatus->name,
                ];
            }),
            'before_work_position' => $this->whenLoaded('beforeWorkPosition', function () {
                return [
                    'id' => $this->beforeWorkPosition->id,
                    'name' => $this->beforeWorkPosition->name,
                ];
            }),
            'before_department' => $this->whenLoaded('beforeDepartment', function () {
                return [
                    'id' => $this->beforeDepartment->id,
                    'name' => $this->beforeDepartment->name,
                ];
            }),
            'before_work_location' => $this->whenLoaded('beforeWorkLocation', function () {
                return [
                    'id' => $this->beforeWorkLocation->id,
                    'name' => $this->beforeWorkLocation->name,
                ];
            }),
            'before_team' => $this->whenLoaded('beforeTeam', function () {
                return [
                    'id' => $this->beforeTeam->id,
                    'name' => $this->beforeTeam->name,
                ];
            }),
            'after_employee_status' => $this->whenLoaded('afterEmployeeStatus', function () {
                return [
                    'id' => $this->afterEmployeeStatus->id,
                    'name' => $this->afterEmployeeStatus->name,
                ];
            }),
            'after_work_position' => $this->whenLoaded('afterWorkPosition', function () {
                return [
                    'id' => $this->afterWorkPosition->id,
                    'name' => $this->afterWorkPosition->name,
                ];
            }),
            'after_department' => $this->whenLoaded('afterDepartment', function () {
                return [
                    'id' => $this->afterDepartment->id,
                    'name' => $this->afterDepartment->name,
                ];
            }),
            'after_work_location' => $this->whenLoaded('afterWorkLocation', function () {
                return [
                    'id' => $this->afterWorkLocation->id,
                    'name' => $this->afterWorkLocation->name,
                ];
            }),
            'after_team' => $this->whenLoaded('afterTeam', function () {
                return [
                    'id' => $this->afterTeam->id,
                    'name' => $this->afterTeam->name,
                ];
            }),
            'career_at' => $this->career_at,
            'note' => $this->note,
            'confirmed_at' => $this->confirmed_at,
            'settled_at' => $this->settled_at,
            
            'status' => $this->status,
            'approvals' => $this->whenLoaded('approvalRequest', function () {
                return ApprovalRequestStepResource::collection($this->approvalRequest?->steps ?? collect([]));
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
