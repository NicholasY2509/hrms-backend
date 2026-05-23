<?php

namespace App\Modules\Organization\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionHierarchyMatrixResource extends JsonResource
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
            'work_location_id' => $this->work_location_id,
            'department_id' => $this->department_id,
            'work_position_id' => $this->work_position_id,
            'supervisor_work_position_id' => $this->supervisor_work_position_id,
            'location' => [
                'id' => $this->workLocation->id ?? null,
                'name' => $this->workLocation->name ?? null,
            ],
            'department' => [
                'id' => $this->department->id ?? null,
                'name' => $this->department->name ?? null,
            ],
            'position' => [
                'id' => $this->workPosition->id ?? null,
                'name' => $this->workPosition->name ?? null,
            ],
            'supervisor_position' => [
                'id' => $this->supervisorWorkPosition->id ?? null,
                'name' => $this->supervisorWorkPosition->name ?? null,
                'employees' => \App\Modules\Employee\Models\Employee::when($this->work_location_id, function ($query) {
                        $query->where('work_location_id', $this->work_location_id);
                    })
                    ->where('work_position_id', $this->supervisor_work_position_id)
                    ->where('work_employee_status_id', 1)
                    ->get(['id', 'first_name', 'last_name', 'work_location_id'])
                    ->map(fn($emp) => [
                        'id' => $emp->id, 
                        'name' => $emp->full_name . ($this->work_location_id === null ? " ({$emp->work_location->name})" : "")
                    ])
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
