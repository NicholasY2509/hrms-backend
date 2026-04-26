<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalPolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'approvable_type' => $this->approvable_type,
            'work_position_id' => $this->work_position_id,
            'is_active' => (bool) $this->is_active,
            'steps' => ApprovalPolicyStepResource::collection($this->whenLoaded('steps')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
