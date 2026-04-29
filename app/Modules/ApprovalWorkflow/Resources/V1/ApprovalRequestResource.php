<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use App\Modules\Overtime\Models\Overtime;
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
            'reference_number' => $this->reference_number,
            'status' => $this->status,
            'current_step_sequence' => $this->current_step_sequence,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'approvable_type' => $this->approvable_type,
            'approvable_id' => $this->approvable_id,
            'approvable' => $this->whenLoaded('approvable'),
            'category' => $this->getCategory(),

            'rule' => new ApprovalRuleResource($this->whenLoaded('rule')),
            'steps' => ApprovalRequestStepResource::collection($this->whenLoaded('steps')),
        ];
    }

    protected function getCategory(): ?string
    {
        if (!$this->relationLoaded('approvable')) {
            return null;
        }

        $model = $this->approvable;

        if (!$model) {
            return null;
        }

        if ($model instanceof Overtime) {
            return $model->type ?? null;
        }

        if ($model->relationLoaded('unpaid_leave_type')) {
            return $model->unpaid_leave_type->name ?? null;
        }

        return null;
    }
}
