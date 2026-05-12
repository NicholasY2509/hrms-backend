<?php

namespace App\Modules\Payroll\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Payroll\Services\PayrollService;

class SalaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $service = app(PayrollService::class);
        $hourlyRate = $service->calculateRate($this->amount_reporting);
        $realHourlyRate = $service->calculateRate($this->amount_actual);

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'amount' => (float) $this->amount_reporting,
            'real_amount' => (float) $this->amount_actual,
            'hourly_rate' => $hourlyRate,
            'real_hourly_rate' => $realHourlyRate,
            'currency' => 'IDR',
            'calculation_factor' => 173,
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
