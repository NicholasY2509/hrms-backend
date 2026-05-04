<?php

namespace App\Modules\UnpaidLeave\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnpaidLeaveTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'background_color' => $this->background_color,
            'border_color' => $this->border_color,
            'text_color' => $this->text_color,
            'limit' => $this->limit,
            'is_annual_leave_deduction' => $this->is_annual_leave_deduction,
        ];
    }
}
