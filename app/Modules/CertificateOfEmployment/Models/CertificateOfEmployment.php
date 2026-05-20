<?php

namespace App\Modules\CertificateOfEmployment\Models;

use App\Modules\Employee\Models\Employee;
use App\Modules\Organization\Models\WorkPosition;
use App\Traits\Approvable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateOfEmployment extends Model
{
    use SoftDeletes, Approvable, HasUuids;

    protected $table = 'certificate_of_employments';
    protected $guarded = [];

    /**
     * Get the employee that owns the CoE.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the work position associated with the CoE.
     */
    public function work_position(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class);
    }

    /**
     * Get the dynamic status of the certificate.
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
    // public function syncApprovalStatus(string $status): void
    // {
    //     if ($status === 'approved') {
    //         $this->update(['confirmed_at' => now()]);
    //     }
    // }
}
