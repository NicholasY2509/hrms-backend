<?php

namespace App\Modules\UnpaidLeave\Services;

use App\Modules\UnpaidLeave\Notifications\UnpaidLeaveApprovalNotification;
use App\Modules\Employee\Models\Employee;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use Illuminate\Support\Facades\Log;

class UnpaidLeaveApprovalService
{
    /**
     * Send notifications to all approvers in the approval chain.
     * 
     * @param UnpaidLeave $leave
     * @return void
     */
    public function generateInitialApprovals(UnpaidLeave $leave): void
    {
        $leave->unsetRelation('approvalRequest');
        $leave->load(['approvalRequest.steps']);
        $request = $leave->approvalRequest;

        if (!$request) {
            return;
        }

        foreach ($request->steps as $step) {
            $this->notifyStepApprovers($leave, $step);
        }
    }

    /**
     * Notify all approvers for a specific step.
     */
    protected function notifyStepApprovers(UnpaidLeave $leave, $step): void
    {
        $approverIds = $step->getResolvedApproverIds();
        
        if (is_array($approverIds)) {
            $employees = Employee::with('user')->whereIn('id', $approverIds)->get();
        } else {
            $employees = Employee::with('user')->where('id', $approverIds)->get();
        }

        foreach ($employees as $employee) {
            if ($employee?->user) {
                $employee->user->notify(new UnpaidLeaveApprovalNotification($leave));
            }
        }
    }
}
