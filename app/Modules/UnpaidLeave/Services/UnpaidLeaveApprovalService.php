<?php

namespace App\Modules\UnpaidLeave\Services;

use App\Modules\UnpaidLeave\Notifications\UnpaidLeaveApprovalNotification;
use App\Modules\Employee\Models\Employee;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use Illuminate\Support\Facades\Log;

class UnpaidLeaveApprovalService
{
    /**
     * Handled by Approvable trait events.
     */
    public function generateInitialApprovals(UnpaidLeave $leave): void
    {
        // Now handled by Approvable trait and ApprovalNotificationListener
    }

    /**
     * Handled by Approvable trait events.
     */
    protected function notifyStepApprovers(UnpaidLeave $leave, $step): void
    {
        // Now handled by Approvable trait and ApprovalNotificationListener
    }
}
