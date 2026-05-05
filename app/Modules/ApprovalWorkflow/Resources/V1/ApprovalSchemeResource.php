<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalSchemeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'model_class' => $this->model_class,
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'rules_count' => $this->whenCounted('rules'),
            'position_rules_count' => $this->whenCounted('position_rules'),
            'department_rules_count' => $this->whenCounted('department_rules'),
            'rules' => ApprovalRuleResource::collection($this->whenLoaded('rules')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
