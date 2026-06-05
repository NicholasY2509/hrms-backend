<?php

namespace App\Modules\Leave\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnualLeaveSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->full_name ?? $this->name,
            'nik' => $this->employee_id_number,
            'balance_before' => $this->formatBalance($this->first_balance_before, $request),
            'balance_after' => [
                'annual_leave_2' => (float) $this->annual_leave_2,
                'annual_leave_3' => (float) $this->annual_leave_3,
            ],
            'total_tambah' => (float) $this->total_tambah,
            'total_potong' => (float) $this->total_potong,
        ];
    }

    protected function formatBalance($balance, Request $request)
    {
        if ($balance === null) {
            return null;
        }

        if (is_scalar($balance)) {
            $year = $request->input('year', date('Y'));
            return (object) [$year => $balance];
        }

        if (is_array($balance)) {
            return (object) $balance;
        }

        return $balance;
    }
}
