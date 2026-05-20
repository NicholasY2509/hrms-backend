<?php

namespace App\Modules\Overtime\Resources\V1;

use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestStepResource;
use App\Modules\Employee\Resources\EmployeeResource;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OvertimeSimpleResource extends JsonResource
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
            'type' => $this->type,
            'document_no' => $this->document_no,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'finish_time' => $this->finish_time,
            'total_time' => $this->total_time,
            'note' => $this->note,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}