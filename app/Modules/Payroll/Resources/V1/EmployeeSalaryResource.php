<?php

namespace App\Modules\Payroll\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'bpjs_base_amount' => (float) $this->bpjs_base_amount,
            'actual_base_amount' => (float) $this->actual_base_amount,
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'reason' => $this->reason,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
