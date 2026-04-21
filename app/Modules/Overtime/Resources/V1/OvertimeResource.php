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
            ],
            'overtime_type' => [
                'id' => $this->overtime_type_id,
                'name' => $this->overtime_type?->name ?? $this->type, // Fallback to raw type string
            ],
            'date' => $this->date,
            'start_time' => $this->start_time,
            'finish_time' => $this->finish_time,
            'total_time' => $this->total_time,
            'note' => $this->note,
            'attachment_urls' => $this->overtime_attachments->map(function ($attachment) {
                return StorageService::url($attachment->path);
            }),
            'status' => $this->status,
            'settled_at' => $this->settled_at,
            'approvals' => $this->overtime_approvals->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'approver_name' => $approval->employee?->full_name ?? $approval->role,
                    'role' => $approval->role,
                    'status' => $approval->status,
                    'note' => $approval->note,
                    'updated_at' => $approval->updated_at?->toDateTimeString(),
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
