<?php

namespace App\Modules\Employee\Models;

use App\Traits\Approvable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resignation extends Model
{
    use SoftDeletes, Approvable;

    protected $table = 'resigns';
    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the dynamic status of the resignation.
     */
    public function getStatusAttribute()
    {
        if ($this->settled_at) {
            return 'Settled';
        }

        $request = $this->approvalRequest;

        if (!$request) {
            return $this->confirmed_at ? 'Pending' : 'Draft';
        }

        if ($request->status === 'pending') {
            $currentStep = $request->currentStep();
            $approverNames = $currentStep ? $currentStep->getResolvedApproverNames() : null;
            
            return $approverNames ? "Pending by {$approverNames}" : 'Pending';
        }

        return match ($request->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => 'Pending',
        };
    }

    /**
     * Sync the approval status with the model's native fields.
     */
    public function syncApprovalStatus(string $status): void
    {
        if ($status === 'approved') {
            $this->update(['confirmed_at' => now()]);
        }
    }
}
