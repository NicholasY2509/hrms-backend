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
            'department' => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ] : null,
            'position' => $this->position ? [
                'id' => $this->position->id,
                'name' => $this->position->name,
            ] : null,
            'work_location' => $this->work_location ? [
                'id' => $this->work_location->id,
                'name' => $this->work_location->name,
            ] : null,
            'work_employee_status' => $this->work_employee_status ? [
                'id' => $this->work_employee_status->id,
                'name' => $this->work_employee_status->name,
            ] : null,
            'work_employee_type' => $this->work_employee_type ? [
                'id' => $this->work_employee_type->id,
                'name' => $this->work_employee_type->name,
            ] : null,
            'email' => $user?->email ?? $this->company_email,
            'company_email' => $this->company_email,
            'photo_url' => $this->profile_url,
            'profileUrl' => $this->profile_url,
            'join_date' => $this->join_date,
            'resign_date' => $this->resign_date,
            'phone_number' => $this->phone_number,
            'handphone' => $this->handphone,
            'current_address' => $this->current_address,
            'place_birth' => $this->place_birth,
            'date_birth' => $this->date_birth,
            'supervisor' => $supervisor ? [
                'id' => $supervisor->id,
                'name' => $supervisor->full_name,
                'nik' => $supervisor->nik,
            ] : null,
        ];
    }
}
