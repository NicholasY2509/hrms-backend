<?php

namespace App\Modules\Payroll\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $factor = 173;
        $bpjsAmount = (float) $this->bpjs_base_amount;
        $actualAmount = (float) $this->actual_base_amount;

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'bpjs_base_amount' => $bpjsAmount,
            'actual_base_amount' => $actualAmount,
            'hourly_rate' => round($bpjsAmount / $factor, 2),
            'real_hourly_rate' => round($actualAmount / $factor, 2),
            'currency' => 'IDR',
            'calculation_factor' => $factor,
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'reason' => $this->reason,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
