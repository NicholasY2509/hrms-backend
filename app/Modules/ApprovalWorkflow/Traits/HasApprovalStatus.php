<?php

namespace App\Modules\ApprovalWorkflow\Traits;

trait HasApprovalStatus
{
    /**
     * Sync the model's status with the approval request status.
     * This is typically used for legacy compatibility where the parent model
     * still maintains its own 'status' and 'settled_at' columns.
     */
    public function syncApprovalStatus(string $status): void
    {
        if ($status === 'approved') {
            $this->update([
                'status' => 'approved', 
                'settled_at' => now()
            ]);
        } elseif ($status === 'rejected') {
            $this->update([
                'status' => 'rejected'
            ]);
        }
    }
}
