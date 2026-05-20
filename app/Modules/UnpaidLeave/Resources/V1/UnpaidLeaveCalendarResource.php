<?php

namespace App\Modules\UnpaidLeave\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnpaidLeaveCalendarResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->full_name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status, // Using the accessor from the model
            'unpaid_leave_type_name' => $this->unpaid_leave_type?->name,
            'color' => $this->unpaid_leave_type?->color ?? '#EF4444', // Default red if none
        ];
    }
}
