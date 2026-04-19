<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLeaveApproval extends Model
{
    use SoftDeletes;

    protected $connection = 'legacy';
    protected $table = 'employee_leave_approvals';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the employee that owns the approval configuration.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the designated approver (employee).
     */
    public function approval(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approval_id', 'id');
    }
}
