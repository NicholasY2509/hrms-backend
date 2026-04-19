<?php

namespace App\Modules\UnpaidLeave\Resources\V1;

use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnpaidLeaveResource extends JsonResource
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
            'employee' => [
                'id' => $this->employee_id,
                'full_name' => $this->employee?->full_name,
            ],
            'type' => [
                'id' => $this->unpaid_leave_type_id,
                'name' => $this->unpaid_leave_type?->name,
            ],
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_days' => $this->total,
            'note' => $this->note,
            'attachment_url' => StorageService::url($this->attachment),
            'confirmed_at' => $this->confirmed_at,
            'settled_at' => $this->settled_at,
            'status' => $this->status,
            'approvals' => $this->unpaid_leave_approvals->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'approver_name' => $approval->employee?->full_name ?? $approval->role,
                    'role' => $approval->role,
                    'status' => $approval->status,
                    'note' => $approval->note,
                    'updated_at' => $approval->updated_at?->toDateTimeString(),
                ];
            }),
        ];
    }
}
