<?php

namespace App\Modules\UnpaidLeave\Resources\V1;

use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestStepResource;
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
            'approvals' => ApprovalRequestStepResource::collection($this->approvalRequest?->steps ?? collect([])),
        ];
    }
}