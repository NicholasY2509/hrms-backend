<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRequestResource extends JsonResource
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
            'status' => $this->status,
            'current_step_sequence' => $this->current_step_sequence,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Polymorphic relation
            'approvable_type' => $this->approvable_type,
            'approvable_id' => $this->approvable_id,
            'approvable' => $this->whenLoaded('approvable'),

            // Rule & Steps
            'rule' => new ApprovalRuleResource($this->whenLoaded('rule')),
            'steps' => ApprovalRequestStepResource::collection($this->whenLoaded('steps')),
        ];
    }
}
