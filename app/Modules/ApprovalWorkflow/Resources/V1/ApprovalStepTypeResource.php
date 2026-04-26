<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalStepTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'needs_target' => (bool) $this->needs_target,
            'description' => $this->description,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
