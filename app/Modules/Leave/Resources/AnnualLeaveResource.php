<?php

namespace App\Modules\Leave\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnualLeaveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->full_name,
                    'nik' => $this->employee->employee_id_number,
                    'annual_leave_2' => $this->employee->annual_leave_2,
                    'annual_leave_3' => $this->employee->annual_leave_3,
                ];
            }),
            'annual_leave_at' => $this->annual_leave_at?->format('Y-m-d'),
            'total' => $this->total,
            'status' => $this->status,
            'description' => $this->keterangan,
            'deduction_details' => collect($this->deduction_details)->map(function ($amount, $year) {
                return [
                    'year' => $year,
                    'amount' => $amount
                ];
            })->values(),
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
