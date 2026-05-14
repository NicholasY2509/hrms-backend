<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePersonalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'full_name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'email' => $this->user_employee?->user?->email,
            'gender' => [
                'id' => $this->gender_id,
                'name' => $this->gender?->name,
            ],
            'marital_status' => [
                'id' => $this->marital_status_id,
                'name' => $this->marital_status?->name,
            ],
            'id_card_number' => $this->id_card_number,
            'religion' => [
                'id' => $this->religion_id,
                'name' => $this->religion?->name,
            ],
            'blood_group' => [
                'id' => $this->blood_group_id,
                'name' => $this->blood_group?->name,
            ],
            'birth_place' => $this->place_birth,
            'birth_date' => $this->date_birth,
            'current_address' => $this->current_address,
            'residence_address' => $this->residence_address,
        ];
    }
}
