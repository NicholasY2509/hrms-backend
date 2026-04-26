<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'members_count' => $this->employees()->count(),
            'employees' => $this->whenLoaded('employees', function () {
                return $this->employees->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->full_name,
                        'nik' => $employee->nik,
                        'job_title' => $employee->position?->name,
                    ];
                });
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
