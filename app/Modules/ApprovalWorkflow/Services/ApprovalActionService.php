<?php

namespace App\Modules\ApprovalWorkflow\Services;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use App\Modules\ApprovalWorkflow\Repositories\ApprovalActionRepository;
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
    public function getMyPendingApprovals(int $perPage = 15)
    {
        $employeeId = Auth::user()->employee->id ?? null;
        if (!$employeeId) return collect([]);

        return $this->repository->getPendingForEmployee($employeeId, $perPage);
    }

    /**
     * Approve a specific request.
     */
    public function approve(int $requestId, ?string $notes = null)
    {
        return DB::transaction(function () use ($requestId, $notes) {
            $employeeId = Auth::user()->employee->id;
            $step = $this->repository->findStepForAction($requestId, $employeeId);

            if (!$step) {
                throw new \Exception("Anda tidak memiliki otoritas untuk menyetujui pengajuan ini atau pengajuan sudah diproses.");
            }

            // 1. Update the Step
            $step->update([
                'status' => 'approved',
                'notes' => $notes,
                'actioned_by' => Auth::id(),
                'actioned_at' => now(),
            ]);

            // 2. Check if all steps are approved
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
    public function reject(int $requestId, string $notes)
    {
        return DB::transaction(function () use ($requestId, $notes) {
            $employeeId = Auth::user()->employee->id;
            $step = $this->repository->findStepForAction($requestId, $employeeId);

            if (!$step) {
                throw new \Exception("Anda tidak memiliki otoritas untuk menolak pengajuan ini.");
            }

            // 1. Update the Step
            $step->update([
                'status' => 'rejected',
                'notes' => $notes,
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
     * Optional: Update the parent model's native status field (Legacy Support).
     */
    protected function syncParentModelStatus(ApprovalRequest $request, string $status)
    {
        $model = $request->approvable;
        if ($model) {
            // Some models might have a status column, others might use settled_at
            if ($status === 'approved') {
                $model->update(['status' => 'approved', 'settled_at' => now()]);
            } else {
                $model->update(['status' => 'rejected']);
            }
        }
    }
}
