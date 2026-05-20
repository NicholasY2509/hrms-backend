<?php

namespace App\Modules\Employee\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeTaxProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'npwp_number' => $this->npwp_number,
            'ptkp_setting' => [
                'id' => $this->ptkp_setting->id,
                'code' => $this->ptkp_setting->code,
                'name' => $this->ptkp_setting->name,
                'ter_category' => [
                    'id' => $this->ptkp_setting->ter_category->id,
                    'name' => $this->ptkp_setting->ter_category->name
                ]
            ],
            'tax_method' => $this->tax_method,
        ];
    }
}
