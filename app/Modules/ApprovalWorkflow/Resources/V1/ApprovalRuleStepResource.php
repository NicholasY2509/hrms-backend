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
            'target_name' => match ($this->type_slug) {
                'supervisor'        => 'Direct Supervisor',
                'dept_head'         => 'Department Head',
                'group'             => $this->group?->name,
                'user', 'employee'  => $this->employee?->full_name,
                'work_position'     => $this->workPosition?->name,
                default             => $this->type_slug,
            },
        ];
    }
}
