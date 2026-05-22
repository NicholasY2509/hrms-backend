<?php

namespace App\Modules\ShiftExchange\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Attendance\Resources\WorkingHourResource;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestResource;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestStepResource;
use App\Modules\Employee\Resources\EmployeeResource;

class ShiftExchangeResource extends JsonResource
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
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'date' => $this->date,
            'original_working_hour_id' => $this->original_working_hour_id,
            'original_working_hour' => new WorkingHourResource($this->whenLoaded('originalWorkingHour')),
            'requested_working_hour_id' => $this->requested_working_hour_id,
            'requested_working_hour' => new WorkingHourResource($this->whenLoaded('requestedWorkingHour')),
            'exchange_with_employee_id' => $this->exchange_with_employee_id,
            'exchange_with_employee' => new EmployeeResource($this->whenLoaded('exchangeWithEmployee')),
            'reason' => $this->reason,
            'status' => $this->status,
            'settled_at' => $this->settled_at,
            'approvals' => ApprovalRequestStepResource::collection($this->approvalRequest?->steps ?? collect([])),
            'created_at' => $this->created_at,
        ];
    }
}
