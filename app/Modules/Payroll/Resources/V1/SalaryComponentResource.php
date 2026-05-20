<?php

namespace App\Modules\Payroll\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'category' => $this->category,
            'type' => $this->type,
            'default_amount' => (float) $this->default_amount,
            'is_taxable' => (bool) $this->is_taxable,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
