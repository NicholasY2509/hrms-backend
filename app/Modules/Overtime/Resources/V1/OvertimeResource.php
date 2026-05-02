<?php

namespace App\Modules\Overtime\Resources\V1;

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
            'employee' => [
                'id' => $this->employee_id,
                'full_name' => $this->employee?->full_name,
                'nik' => $this->employee?->employee_id_number,
                'department' => [
                    'id' => $this->employee?->department_id,
                    'name' => $this->employee?->department?->name,
                ],
                'position' => [
                    'id' => $this->employee?->work_position_id,
                    'name' => $this->employee?->position?->name,
                ],
            ],
            'document_no' => $this->document_no,
            'overtime_type' => $this->type,
            'dac_type' => $this->overtime_type?->name ?? null,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'finish_time' => $this->finish_time,
            'total_time' => $this->total_time,
            'estimated_overtime_price' => $this->estimated_overtime_price,
            'real_overtime_price' => $this->real_overtime_price,
            'note' => $this->note,
            'attachment_urls' => $this->overtime_attachments->map(function ($attachment) {
                return StorageService::url($attachment->path);
            }),
            'status' => $this->status,
            'settled_at' => $this->settled_at,
            'approvals' => $this->approvalRequest?->steps->map(function ($step) {
                return [
                    'id' => $step->id,
                    'approver_name' => $step->actor?->name ?? ($step->approver_type === 'group'
                        ? $step->group?->employees->pluck('full_name')->join(', ') ?: 'No members'
                        : $step->approver?->full_name),
                    'approver_id' => $step->getResolvedApproverIds(),
                    'role' => $step->approver_type,
                    'status' => $step->status,
                    'note' => $step->notes,
                    'updated_at' => $step->actioned_at?->toDateTimeString() ?? $step->updated_at?->toDateTimeString(),
                ];
            }) ?? [],
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
