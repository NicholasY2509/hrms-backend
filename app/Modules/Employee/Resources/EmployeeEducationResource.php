<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeEducationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'study' => $this->study,
            'start_year' => $this->start_year,
            'end_year' => $this->end_year,
            'school_name' => $this->school_name,
        ];
    }
}
