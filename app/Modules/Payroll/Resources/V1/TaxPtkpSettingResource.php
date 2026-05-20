<?php

namespace App\Modules\Payroll\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxPtkpSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'amount' => $this->amount,
            'ter_category' => $this->ter_category?->name,
            'description' => "{$this->code} - {$this->name} (TER {$this->ter_category?->name})",
        ];
    }
}
