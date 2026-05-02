<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use App\Modules\Organization\Resources\WorkPositionResource;
use App\Modules\Organization\Resources\WorkLocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'approval_scheme_id' => $this->approval_scheme_id,
            'work_position_id' => $this->work_position_id,
            'work_position' => new WorkPositionResource($this->whenLoaded('workPosition')),
            'work_location_id' => $this->work_location_id,
            'work_location' => new WorkLocationResource($this->whenLoaded('workLocation')),
            'is_default' => (bool) $this->is_default,
            'is_active' => (bool) $this->is_active,
            'steps' => ApprovalRuleStepResource::collection($this->whenLoaded('steps')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
