<?php

namespace App\Modules\System\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'progress' => $this->progress,
            'message' => $this->message,
            'payload' => $this->payload,
            'metadata' => $this->metadata,
            'completed_at' => $this->completed_at ? $this->completed_at->toIso8601String() : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
            'user' => [
                'id' => $this->user_id,
                'name' => $this->whenLoaded('user', fn () => $this->user->user_employee->employee->full_name),
                'email' => $this->whenLoaded('user', fn () => $this->user->email),
            ],
        ];
    }
}
