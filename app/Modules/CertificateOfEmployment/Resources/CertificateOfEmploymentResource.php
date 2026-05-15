<?php

namespace App\Modules\CertificateOfEmployment\Resources;

use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestStepResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateOfEmploymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_no' => $this->document_no,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->full_name,
                    'nik' => $this->employee->employee_id_number,
                ];
            }),
            'work_position_id' => $this->work_position_id,
            'work_position' => $this->whenLoaded('work_position', function () {
                return [
                    'id' => $this->work_position->id,
                    'name' => $this->work_position->name,
                ];
            }),
            'request_date' => $this->request_date,
            'status' => $this->status,
            'confirmed_at' => $this->confirmed_at,
            'settled_at' => $this->settled_at,
            'approvals' => $this->whenLoaded('approvalRequest', function () {
                return ApprovalRequestStepResource::collection($this->approvalRequest?->steps ?? collect([]));
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
