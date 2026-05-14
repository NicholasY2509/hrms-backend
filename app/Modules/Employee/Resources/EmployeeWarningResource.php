<?php

namespace App\Modules\Employee\Resources;

use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeWarningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'attachment' => $this->attachment,
            'attachment_url' => StorageService::url($this->attachment),
            'warning_at' => $this->warning_at,
            'confirmed_at' => $this->confirmed_at,
            'settled_at' => $this->settled_at,
            'document_no' => $this->document_no,
            'warning_letter_type' => [
                'id' => $this->warning_letter_type?->id,
                'name' => $this->warning_letter_type?->name
            ],
        ];
    }
}
