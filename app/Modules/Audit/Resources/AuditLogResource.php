<?php

namespace App\Modules\Audit\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $causerName = 'System';

        if ($this->causer) {
            $causerName = $this->causer->name 
                ?? $this->causer->user_employee->employee->full_name 
                ?? 'User #' . $this->causer_id;
        }

        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'event' => $this->event,
            'causer' => [
                'id' => $this->causer_id,
                'name' => $causerName,
                'type' => $this->causer_type,
            ],
            'properties' => $this->properties,
            'attribute_changes' => $this->attribute_changes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'human_time' => $this->created_at->diffForHumans(),
        ];
    }
}
