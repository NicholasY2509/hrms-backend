<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use App\Modules\System\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRequestStepResource extends JsonResource
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
            'approver_type' => $this->approver_type,
            'approver_id' => $this->approver_id,
            'sequence' => $this->sequence,
            'status' => $this->status,
            'notes' => $this->notes,
            'actioned_at' => $this->actioned_at,
            'actor' => new UserResource($this->whenLoaded('actor')),
        ];
    }
}
