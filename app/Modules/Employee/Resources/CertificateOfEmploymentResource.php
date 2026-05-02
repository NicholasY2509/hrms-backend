<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CertificateOfEmploymentResource extends JsonResource
{
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
            'work_position' => $this->whenLoaded('workPosition', function () {
                return [
                    'id' => $this->workPosition->id,
                    'name' => $this->workPosition->name,
                ];
            }),
            
            'request_date' => $this->request_date,
            'issued_date' => $this->issued_date,
            'note' => $this->note,
            
            'attachment' => $this->attachment,
            'attachment_url' => $this->attachment ? Storage::url($this->attachment) : null,
            
            'settled_at' => $this->settled_at,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
