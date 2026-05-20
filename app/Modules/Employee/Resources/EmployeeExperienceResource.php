<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeExperienceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'office_name' => $this->office_name,
            'office_address' => $this->office_address,
            'office_phone' => $this->office_phone,
            'start_year' => $this->start_year,
            'end_year' => $this->end_year,
            'work_position' => $this->work_position,
            'reason' => $this->reason,
        ];
    }
}
