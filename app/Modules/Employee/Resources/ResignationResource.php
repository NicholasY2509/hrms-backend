<?php

namespace App\Modules\Employee\Resources;

use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestStepResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ResignationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->full_name,
                    'nik' => $this->employee->employee_id_number,
                ];
            }),
            
            'effective_date' => $this->effective_date,
            'reason' => $this->reason,
            
            'attachment' => $this->attachment,
            'attachment_url' => $this->attachment ? Storage::url($this->attachment) : null,
            
            'confirmed_at' => $this->confirmed_at,
            'settled_at' => $this->settled_at,
            'status' => $this->status,
            'approvals' => $this->whenLoaded('approvalRequest', function () {
                return ApprovalRequestStepResource::collection($this->approvalRequest?->steps ?? collect([]));
            }),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
