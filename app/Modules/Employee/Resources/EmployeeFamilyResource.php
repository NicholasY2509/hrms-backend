<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeFamilyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'family_relationship' => [
                'id' => $this->relationship->id,
                'name' => $this->relationship->name
            ],
            'full_name' => $this->full_name,
            'gender' => [
                'id' => $this->gender->id,
                'name' => $this->gender->name
            ],
            'place_birth' => $this->place_birth,
            'date_birth' => $this->date_birth,
            'id_card_number' => $this->id_card_number
        ];
    }
}
