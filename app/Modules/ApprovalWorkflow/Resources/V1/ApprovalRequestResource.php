<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use App\Modules\Employee\Resources\EmployeeResource;
use App\Modules\Overtime\Models\Overtime;
use App\Modules\Overtime\Resources\V1\OvertimeResource;
use App\Modules\UnpaidLeave\Resources\V1\UnpaidLeaveResource;
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
            'approvable' => $this->whenLoaded('approvable', function () {
                $approvable = $this->approvable;
                if (!$approvable) return null;
                if ($approvable instanceof Overtime) {
                    return new OvertimeResource($approvable);
                }
                if ($approvable instanceof UnpaidLeave) {
                    return new UnpaidLeaveResource($approvable);
                }
                $data = $approvable->toArray();
                if ($approvable->relationLoaded('employee')) {
                    $data['employee'] = new EmployeeResource($approvable->employee);
                }
                return $data;
            }),
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
