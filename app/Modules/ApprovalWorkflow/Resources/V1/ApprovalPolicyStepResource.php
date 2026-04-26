<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalPolicyStepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'sequence' => $this->sequence,
            'target_id' => $this->target_id,
            'target_name' => $this->when($this->type === 'group', function () {
                return $this->group?->name;
            }),
            // Logic for 'user' type target name could be added here if needed
        ];
    }
}
