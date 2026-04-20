<?php

namespace App\Modules\Employee\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->user_employee?->user;
        $supervisor = $this->supervisor?->employee;

        return [
            'id' => $this->id,
            'nik' => $this->nik,
            'name' => $this->full_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'job_title' => $this->job_title ?? $this->position,
            'department' => $this->department?->name ?? 'N/A',
            'email' => $user?->email,
            'photo_url' => $this->photo_url,
            'join_date' => $this->join_date,
            'supervisor' => $supervisor ? [
                'id' => $supervisor->id,
                'name' => $supervisor->name,
                'nik' => $supervisor->nik,
            ] : null,
        ];
    }
}
