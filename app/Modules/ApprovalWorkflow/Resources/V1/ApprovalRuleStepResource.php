<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRuleStepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type_slug' => $this->type_slug,
            'sequence' => $this->sequence,
            'target_id' => $this->target_id,
            'target_name' => $this->when($this->type_slug === 'group', function () {
                return $this->group?->name;
            }, function () {
                return $this->when($this->type_slug === 'user', function () {
                    return $this->employee?->full_name;
                });
            }),
        ];
    }
}
