<?php

namespace App\Modules\ApprovalWorkflow\Services;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use App\Modules\ApprovalWorkflow\Repositories\ApprovalActionRepository;
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
    public function getMyPendingApprovals(int $perPage = 15, ?string $type = null)
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
    protected function validateAuthority(int $requestId, string $action): ApprovalRequestStep
    {
        $employeeId = Auth::user()->employee->id;
        
        $userSteps = ApprovalRequestStep::where('approval_request_id', $requestId)
            ->where(function ($query) use ($employeeId) {
                $employee = \App\Modules\Employee\Models\Employee::find($employeeId);
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
            })->get();

        $actionText = $action === 'approve' ? 'menyetujui' : 'menolak';

        if ($userSteps->isEmpty()) {
            throw new \Exception("Anda tidak memiliki otoritas untuk {$actionText} pengajuan ini.");
        }

        $step = $userSteps->where('sequence', ApprovalRequest::find($requestId)?->current_step_sequence)
            ->where('status', 'pending')
            ->first();

        if (!$step) {
            throw new \Exception("Belum giliran Anda untuk {$actionText} pengajuan ini. Tunggu persetujuan sebelumnya diselesaikan.");
        }

        return $step;
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
