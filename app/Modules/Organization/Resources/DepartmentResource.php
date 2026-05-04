<?php

namespace App\Modules\Organization\Resources;

use App\Modules\Employee\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
            'dept_head_id' => $this->dept_head_id,
            'head' => new EmployeeResource($this->whenLoaded('head')),
            'employees_count' => $this->whenCounted('employees'),
        ];
    }
}
