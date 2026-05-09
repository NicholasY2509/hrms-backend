<?php

namespace App\Modules\Employee\Resources;

use App\Modules\Organization\Resources\DepartmentResource;
use App\Modules\Organization\Resources\WorkLocationResource;
use App\Modules\Organization\Resources\WorkPositionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->user_employee?->user;
        $supervisor = $this->supervisor?->employee;

        return [
            'id' => $this->id,
            'nik' => $this->nik,
            'employee_id_number' => $this->employee_id_number,
            'id_card_number' => $this->id_card_number,
            'name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'initial_name' => $this->initial_name,
            'job_title' => $this->position ? new WorkPositionResource($this->position) : null,
            'department' => $this->department ? new DepartmentResource($this->department) : null,
            'work_location' => $this->work_location ? new WorkLocationResource($this->work_location) : null,
            'email' => $user?->email ?? $this->company_email,
            'company_email' => $this->company_email,
            'photo_url' => $this->profile_url,
            'profileUrl' => $this->profile_url,
            'join_date' => $this->join_date,
            'resign_date' => $this->resign_date,
            'phone_number' => $this->phone_number,
            'handphone' => $this->handphone,
            'address' => $this->current_address,
            'current_address' => $this->current_address,
            'residence_address' => $this->residence_address,
            'place_birth' => $this->place_birth,
            'date_birth' => $this->date_birth,
            'gender_id' => $this->gender_id,
            'marital_status_id' => $this->marital_status_id,
            'religion_id' => $this->religion_id,
            'blood_group_id' => $this->blood_group_id,
            'annual_leave_2' => $this->annual_leave_2,
            'annual_leave_3' => $this->annual_leave_3,
            'is_get_annual_leave' => (bool) $this->is_get_annual_leave,
            'supervisor' => $supervisor ? [
                'id' => $supervisor->id,
                'name' => $supervisor->full_name,
                'nik' => $supervisor->nik,
            ] : null,
        ];
    }
}
