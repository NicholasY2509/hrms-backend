<?php

namespace App\Modules\UnpaidLeave\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnpaidLeaveApproval extends Model
{
    use SoftDeletes;

    protected $connection = 'legacy';
    protected $table = 'unpaid_leave_approvals';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the unpaid leave that owns the approval.
     */
    public function unpaid_leave(): BelongsTo
    {
        return $this->belongsTo(UnpaidLeave::class, 'unpaid_leave_id', 'id');
    }

    /**
     * Get the employee that performed the approval.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
