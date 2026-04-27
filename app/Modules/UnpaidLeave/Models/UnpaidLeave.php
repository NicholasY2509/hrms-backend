<?php

namespace App\Modules\UnpaidLeave\Models;

use App\Modules\Employee\Models\Employee;
use App\Traits\Approvable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnpaidLeave extends Model
{
    use SoftDeletes, Approvable;

    protected $table = 'unpaid_leaves';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the employee that owns the UnpaidLeave.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the unpaid_leave_type that owns the UnpaidLeave.
     */
    public function unpaid_leave_type(): BelongsTo
    {
        return $this->belongsTo(UnpaidLeaveType::class, 'unpaid_leave_type_id', 'id');
    }

    /**
     * Get the approvals for the UnpaidLeave.
     */
    public function unpaid_leave_approvals()
    {
        return $this->hasMany(UnpaidLeaveApproval::class, 'unpaid_leave_id', 'id');
    }

    /**
     * Get the dynamic status of the unpaid leave (Legacy Port).
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

        // Map internal status to display status
            return match ($request->status) {
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'cancelled' => 'Cancelled',
                default => 'Pending',
            };
        }
}
