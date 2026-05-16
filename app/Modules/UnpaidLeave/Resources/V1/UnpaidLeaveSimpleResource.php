<?php

namespace App\Modules\UnpaidLeave\Resources\V1;

use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestStepResource;
use App\Modules\Employee\Resources\EmployeeResource;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnpaidLeaveSimpleResource extends JsonResource
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
                'id' => $this->employee->id,
                'full_name' => $this->employee->full_name,
                'avatar_url' => StorageService::url($this->employee->avatar),
                'employee_id_number' => $this->employee->employee_id_number,
                'department' => [
                    'id' => $this->employee->department?->id,
                    'name' => $this->employee->department?->name,
                ],
                'position' => [
                    'id' => $this->employee->position?->id,
                    'name' => $this->employee->position?->name,
                ],
            ],
            'type' => [
                'id' => $this->unpaid_leave_type_id,
                'name' => $this->unpaid_leave_type?->name,
            ],
            'created_at' => $this->created_at,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_days' => $this->total,
            'note' => $this->note,
            'status' => $this->status,
        ];
    }
}