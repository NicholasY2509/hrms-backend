<?php

namespace App\Modules\Overtime\Resources\V1;

use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestStepResource;
use App\Modules\Employee\Resources\EmployeeResource;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OvertimeResource extends JsonResource
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
            'document_no' => $this->document_no,
            'type' => [
                'id' => $this->overtime_type_id,
                'name' => $this->overtime_type?->name ?? $this->type,
            ],
            'date' => $this->date,
            'start_time' => $this->start_time,
            'finish_time' => $this->finish_time,
            'total_time' => $this->total_time,
            'estimated_overtime_price' => $this->estimated_overtime_price,
            'real_overtime_price' => $this->real_overtime_price,
            'note' => $this->note,
            'attachments' => $this->overtime_attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'url' => StorageService::url($attachment->path),
                ];
            }),
            'status' => $this->status,
            'settled_at' => $this->settled_at,
            'approvals' => ApprovalRequestStepResource::collection($this->approvalRequest?->steps ?? collect([])),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}