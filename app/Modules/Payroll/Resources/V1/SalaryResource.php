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
        $hourlyRate = $service->calculateRate($this->amount);
        $realHourlyRate = $service->calculateRate($this->real_amount);

        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'amount' => $this->amount,
            'real_amount' => $this->real_amount,
            'hourly_rate' => $hourlyRate,
            'real_hourly_rate' => $realHourlyRate,
            'currency' => 'IDR',
            'calculation_factor' => 173,
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
