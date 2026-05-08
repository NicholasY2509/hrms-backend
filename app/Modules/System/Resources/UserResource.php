<?php

namespace App\Modules\System\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->employee?->full_name,
            'email' => $this->email,
            'is_linked_to_employee' => (bool) $this->user_employee,
            'employee_id' => $this->user_employee?->employee_id,
            'profileUrl' => $this->employee?->profile_url,
            'roles' => $this->remote_roles ?? [],
        ];
    }
}
