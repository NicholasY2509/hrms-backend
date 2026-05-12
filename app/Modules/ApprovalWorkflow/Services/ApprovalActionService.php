<?php

namespace App\Modules\ApprovalWorkflow\Services;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use App\Modules\ApprovalWorkflow\Repositories\ApprovalActionRepository;
use App\Modules\Employee\Models\Employee;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ApprovalActionService
{
    protected ApprovalActionRepository $repository;

    public function __construct(ApprovalActionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get pending requests for the authenticated user.
     */
    public function getMyPendingApprovals(int $perPage = 15, $type = null)
    {
        $employeeId = Auth::user()->employee->id ?? null;
        if (!$employeeId) return collect([]);

        return $this->repository->getPendingForEmployee($employeeId, $perPage, $type);
    }

    /**
     * Approve a specific request.
     */
    public function approve(int $requestId, ?string $notes = null, ?UploadedFile $attachment = null)
    {
        return DB::transaction(function () use ($requestId, $notes, $attachment) {
            $step = $this->validateAuthority($requestId, 'approve');

            $attachmentPath = $attachment 
                ? StorageService::store($attachment, 'approvals') 
                : null;

            // 1. Update the Step
            $step->update([
                'status' => 'approved',
                'notes' => $notes,
                'attachment' => $attachmentPath,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
            ]);

            $request = $step->request;
            $allApproved = !$request->steps()->where('status', '!=', 'approved')->exists();

            if ($allApproved) {
                $request->update(['status' => 'approved']);
                $this->syncParentModelStatus($request, 'approved');
            } else {
                // Move sequence forward if this was the current one
                if ($step->sequence == $request->current_step_sequence) {
                    $request->increment('current_step_sequence');
                }
            }

            return $request;
        });
    }

    /**
     * Reject a specific request.
     */
    public function reject(int $requestId, string $notes, ?UploadedFile $attachment = null)
    {
        return DB::transaction(function () use ($requestId, $notes, $attachment) {
            $step = $this->validateAuthority($requestId, 'reject');

            $attachmentPath = $attachment 
                ? StorageService::store($attachment, 'approvals') 
                : null;

            // 1. Update the Step
            $step->update([
                'status' => 'rejected',
                'notes' => $notes,
                'attachment' => $attachmentPath,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
            ]);

            // 2. Reject the whole Request
            $request = $step->request;
            $request->update(['status' => 'rejected']);
            
            // 3. Sync with Parent Model
            $this->syncParentModelStatus($request, 'rejected');

            return $request;
        });
    }

    /**
     * Validates if the user has authority to act on a request.
     * Throws specific exceptions for better user feedback.
     */
    protected function validateAuthority(int $id, string $action): ApprovalRequestStep
    {
        $employeeId = Auth::user()->employee->id;
        $providedStep = ApprovalRequestStep::find($id);
        $requestId = $providedStep ? $providedStep->approval_request_id : $id;
        $request = ApprovalRequest::find($requestId);

        if (!$request) {
            throw new \Exception("Pengajuan tidak ditemukan.");
        }

        $actionText = $action === 'approve' ? 'menyetujui' : 'menolak';

        // 1. If a specific Step ID was provided, strictly validate that step
        if ($providedStep) {
            $isAuthorized = $this->isEmployeeAuthorizedForStep($providedStep, $employeeId);
            
            if (!$isAuthorized) {
                throw new \Exception("Anda tidak memiliki otoritas untuk {$actionText} langkah persetujuan ini.");
            }

            if ($providedStep->status !== 'pending') {
                throw new \Exception("Langkah persetujuan ini sudah diproses.");
            }

            if ($providedStep->sequence != $request->current_step_sequence) {
                throw new \Exception("Belum giliran Anda untuk {$actionText} pengajuan ini. Tunggu persetujuan sebelumnya diselesaikan.");
            }

            return $providedStep;
        }

        // 2. If a Request ID was provided, find the currently actionable step for this user
        $userSteps = ApprovalRequestStep::where('approval_request_id', $requestId)
            ->where(function ($query) use ($employeeId) {
                $this->applyAuthorizerFilter($query, $employeeId);
            })->get();

        if ($userSteps->isEmpty()) {
            throw new \Exception("Anda tidak memiliki otoritas untuk {$actionText} pengajuan ini.");
        }

        $currentStep = $userSteps->where('sequence', $request->current_step_sequence)
            ->where('status', 'pending')
            ->first();

        if (!$currentStep) {
            throw new \Exception("Belum giliran Anda untuk {$actionText} pengajuan ini. Tunggu persetujuan sebelumnya diselesaikan.");
        }

        return $currentStep;
    }

    /**
     * Helper to check if employee is authorized for a specific step.
     */
    protected function isEmployeeAuthorizedForStep(ApprovalRequestStep $step, int $employeeId): bool
    {
        $employee = Employee::find($employeeId);
        $workPositionId = $employee->work_position_id ?? null;
        $groupIds = DB::table('approval_group_employees')
            ->where('employee_id', $employeeId)
            ->pluck('approval_group_id')
            ->toArray();

        if (in_array($step->approver_type, ['user', 'employee', 'supervisor', 'dept_head'])) {
            return $step->approver_id == $employeeId;
        }

        if ($step->approver_type === 'group') {
            return in_array($step->approver_id, $groupIds);
        }

        if ($step->approver_type === 'work_position') {
            return $step->approver_id == $workPositionId;
        }

        return false;
    }

    /**
     * Helper to apply authorizer filter to a query.
     */
    protected function applyAuthorizerFilter($query, int $employeeId)
    {
        $employee = Employee::find($employeeId);
        $workPositionId = $employee->work_position_id ?? null;
        $groupIds = DB::table('approval_group_employees')
            ->where('employee_id', $employeeId)
            ->pluck('approval_group_id')
            ->toArray();

        $query->where(function ($q) use ($employeeId) {
            $q->whereIn('approver_type', ['user', 'employee', 'supervisor', 'dept_head'])
              ->where('approver_id', $employeeId);
        })
        ->orWhere(function ($q) use ($groupIds) {
            $q->where('approver_type', 'group')
              ->whereIn('approver_id', $groupIds);
        })
        ->when($workPositionId, function ($q) use ($workPositionId) {
            $q->orWhere(function ($inner) use ($workPositionId) {
                $inner->where('approver_type', 'work_position')
                      ->where('approver_id', $workPositionId);
            });
        });
    }

    /**
     * Optional: Update the parent model's native status field (Legacy Support).
     */
    protected function syncParentModelStatus(ApprovalRequest $request, string $status)
    {
        $model = $request->approvable;
        
        if ($model && method_exists($model, 'syncApprovalStatus')) {
            $model->syncApprovalStatus($status);
        }
    }
}
