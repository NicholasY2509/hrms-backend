<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use App\Modules\System\Resources\UserResource;
use App\Services\StorageService;
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
            'approver_name' => $this->getResolvedApproverNames(),
            'approver_id' => $this->getResolvedApproverIds(),
            'role' => $this->approver_type,
            'sequence' => $this->sequence,
            'status' => $this->status,
            'note' => $this->notes,
            'attachment' => $this->attachment,
            'attachment_url' => $this->attachment ? StorageService::url($this->attachment) : null,
            'is_current' => $this->sequence == $this->request->current_step_sequence,
            'updated_at' => $this->actioned_at?->toDateTimeString() ?? $this->updated_at?->toDateTimeString(),
            'actor' => new UserResource($this->whenLoaded('actor')),
        ];
    }
}
