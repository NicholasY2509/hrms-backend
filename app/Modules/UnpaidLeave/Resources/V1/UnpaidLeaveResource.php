<?php

namespace App\Modules\UnpaidLeave\Resources\V1;

use App\Modules\Employee\Resources\EmployeeResource;
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
            'employee' => new EmployeeResource($this->employee),
            'type' => [
                'id' => $this->unpaid_leave_type_id,
                'name' => $this->unpaid_leave_type?->name,
            ],
            'date' => $this->created_at,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_days' => $this->total,
            'note' => $this->note,
            'attachment_url' => StorageService::url($this->attachment),
            'confirmed_at' => $this->confirmed_at,
            'settled_at' => $this->settled_at,
            'status' => $this->status,
            'approvals' => $this->approvalRequest?->steps->map(function ($step) {
                return [
                    'id' => $step->id,
                    'approver_name' => $step->getResolvedApproverNames(),
                    'approver_id' => $step->getResolvedApproverIds(),
                    'role' => $step->approver_type,
                    'status' => $step->status,
                    'note' => $step->notes,
                    'updated_at' => $step->actioned_at?->toDateTimeString() ?? $step->updated_at?->toDateTimeString(),
                ];
            }) ?? [],
        ];
    }
}
