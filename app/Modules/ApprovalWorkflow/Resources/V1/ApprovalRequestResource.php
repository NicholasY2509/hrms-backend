<?php

namespace App\Modules\ApprovalWorkflow\Resources\V1;

use App\Modules\Career\Models\Career;
use App\Modules\Career\Resources\CareerResource;
use App\Modules\CertificateOfEmployment\Models\CertificateOfEmployment;
use App\Modules\CertificateOfEmployment\Resources\CertificateOfEmploymentResource;
use App\Modules\Disciplinary\Models\WarningLetter;
use App\Modules\Disciplinary\Resources\WarningLetterResource;
use App\Modules\Employee\Models\Resignation;
use App\Modules\Employee\Resources\ResignationResource;
use App\Modules\Employee\Resources\EmployeeResource;
use App\Modules\Leave\Models\PaidLeaveReversal;
use App\Modules\Leave\Resources\PaidLeaveReversalResource;
use App\Modules\Overtime\Models\Overtime;
use App\Modules\Overtime\Resources\V1\OvertimeSimpleResource;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use App\Modules\UnpaidLeave\Resources\V1\UnpaidLeaveSimpleResource;
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
            'status' => $this->approvable?->status ?? $this->status,
            'user_step_status' => $this->getUserStepStatus(),
            'current_step_sequence' => $this->current_step_sequence,
            'approvable_request_step_id' => $this->getApprovableRequestStepId(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'approvable_type' => $this->approvable_type,
            'approvable_id' => $this->approvable_id,
            'approvable' => $this->whenLoaded('approvable', function () {
                $approvable = $this->approvable;
                if (!$approvable) return null;
                return match (true) {
                    $approvable instanceof Overtime => new OvertimeSimpleResource($approvable),
                    $approvable instanceof UnpaidLeave => new UnpaidLeaveSimpleResource($approvable),
                    $approvable instanceof Career => new CareerResource($approvable),
                    $approvable instanceof WarningLetter => new WarningLetterResource($approvable),
                    $approvable instanceof CertificateOfEmployment => new CertificateOfEmploymentResource($approvable),
                    $approvable instanceof PaidLeaveReversal => new PaidLeaveReversalResource($approvable),
                    $approvable instanceof Resignation => new ResignationResource($approvable),
                    default => $this->transformFallback($approvable),
                };
            }),
            'category' => $this->getCategory(),
            'user_action' => $this->getUserActionDetails(),
            // 'rule' => new ApprovalRuleResource($this->whenLoaded('rule')),
            // 'steps' => ApprovalRequestStepResource::collection($this->whenLoaded('steps')),
        ];
    }

    protected function getUserActionDetails(): ?array
    {
        $step = $this->getUserStep();
        if (!$step) return null;

        return [
            'step_id' => $step->id,
            'status' => $step->status,
            'sequence' => $step->sequence,
            'notes' => $step->notes,
            'actioned_at' => $step->actioned_at,
            'is_current' => $step->sequence == $this->current_step_sequence,
        ];
    }

    protected function getUserStep()
    {
        if (!$this->relationLoaded('steps')) return null;

        $user = auth()->user();
        if (!$user) return null;
        
        $employeeId = $user->employee->id ?? null;
        if (!$employeeId) return null;

        // 1. Priority: Current sequence step for the user
        $step = $this->steps->where('sequence', $this->current_step_sequence)
            ->first(function ($step) use ($employeeId) {
                return $step->isAuthorized($employeeId);
            });

        if ($step) return $step;

        // 2. Fallback: Any future pending step
        $step = $this->steps->where('status', 'pending')
            ->where('sequence', '>', $this->current_step_sequence)
            ->first(function ($step) use ($employeeId) {
                return $step->isAuthorized($employeeId);
            });
            
        if ($step) return $step;

        // 3. Fallback: Any step they were involved in (for history/ongoing)
        return $this->steps->first(function ($step) use ($employeeId) {
            return $step->isAuthorized($employeeId);
        });
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

        return match (true) {
            $model instanceof Overtime => 'Lembur',
            $model instanceof UnpaidLeave => $model->unpaid_leave_type->name ?? 'Izin/Cuti',
            $model instanceof Career => 'Transisi Karir',
            $model instanceof WarningLetter => 'Surat Peringatan',
            $model instanceof CertificateOfEmployment => 'Surat Keterangan Kerja',
            $model instanceof PaidLeaveReversal => 'Pengembalian Hak Cuti',
            $model instanceof Resignation => 'Pengunduran Diri',
            default => null,
        };
    }

    protected function getApprovableRequestStepId(): ?int
    {
        $step = $this->getUserStep();
        
        // Only return an ID if the step is actually actionable (current sequence + pending)
        if ($step && $step->status === 'pending' && $step->sequence == $this->current_step_sequence) {
            return $step->id;
        }

        return null;
    }

    protected function getUserStepStatus(): ?string
    {
        $step = $this->getUserStep();
        return $step ? $step->status : null;
    }

    protected function transformFallback($approvable): array
    {
        $data = $approvable->toArray();
        if ($approvable->relationLoaded('employee')) {
            $data['employee'] = new EmployeeResource($approvable->employee);
        }
        return $data;
    }
}
