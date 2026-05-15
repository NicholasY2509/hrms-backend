<?php

namespace App\Modules\Disciplinary\Resources;

use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestStepResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class WarningLetterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_no' => $this->document_no,
            'name' => $this->name,
            
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->full_name,
                    'nik' => $this->employee->employee_id_number,
                ];
            }),
            
            'warning_letter_type_id' => $this->warning_letter_type_id,
            'warning_letter_type' => $this->whenLoaded('warning_letter_type', function () {
                return [
                    'id' => $this->warning_letter_type->id,
                    'name' => $this->warning_letter_type->name,
                ];
            }),
            
            'warning_at' => $this->warning_at,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'note' => $this->note,
            
            'attachment' => $this->attachment,
            'attachment_url' => $this->attachment ? Storage::url($this->attachment) : null,
            
            'status' => $this->status,
            'approvals' => $this->whenLoaded('approvalRequest', function () {
                return ApprovalRequestStepResource::collection($this->approvalRequest?->steps ?? collect([]));
            }),

            'confirmed_at' => $this->confirmed_at,
            'settled_at' => $this->settled_at,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
