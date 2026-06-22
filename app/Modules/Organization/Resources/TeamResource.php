<?php

namespace App\Modules\Organization\Resources;

use App\Modules\Employee\Resources\EmployeeResource;
use App\Modules\Employee\Resources\EmployeeSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'name' => $this->name,
            'work_location_id' => $this->work_location_id,
            'team_head_id' => $this->team_head_id,
            'team_head_initial_name' => $this->whenLoaded('head', fn() => $this->head->initial_name ?? null),
            'work_location' => new WorkLocationResource($this->whenLoaded('workLocation')),
            'head' => new EmployeeResource($this->whenLoaded('head')),
            'employees' => EmployeeSimpleResource::collection($this->whenLoaded('employees')),
            'employees_count' => $this->whenCounted('employees'),
        ];
    }
}
