<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeEmergencyContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'current_address' => $this->current_address,
            'phone_number' => $this->phone_number,
            'family_relationship' => [
                'id' => $this->relationship?->id,
                'name' => $this->relationship?->name,
            ],
        ];
    }
}
