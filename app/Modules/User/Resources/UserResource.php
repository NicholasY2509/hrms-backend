<?php

namespace App\Modules\User\Resources;

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
            'email' => $this->email,
            'is_linked_to_employee' => (bool) $this->user_employee,
            'employee_id' => $this->whenLoaded('user_employee', fn () => $this->user_employee?->employee_id),
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'name' => $this->employee->full_name,
                    'nik' => $this->employee->employee_id_number,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
