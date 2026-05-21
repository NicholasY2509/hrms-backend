<?php

namespace App\Modules\Payroll\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalaryComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'salary_component_id' => $this->salary_component_id,
            'amount' => (float) $this->amount,
            'is_calculated' => (bool) $this->is_calculated,
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'salary_component' => new SalaryComponentResource($this->whenLoaded('component')),
        ];
    }
}
