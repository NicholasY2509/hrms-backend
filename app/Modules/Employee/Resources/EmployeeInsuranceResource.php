<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeInsuranceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'is_bpjs_kesehatan' => (bool) $this->is_bpjs_kesehatan,
            'is_bpjs_ketenagakerjaan' => (bool) $this->is_bpjs_ketenagakerjaan,
            'insurances' => EmployeeInsuranceDetailResource::collection($this->insurances),
        ];
    }
}
