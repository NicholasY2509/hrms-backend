<?php

namespace App\Modules\Overtime\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeApproval extends Model
{
    use SoftDeletes;

    protected $table = 'overtime_approvals';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the overtime that owns the approval.
     */
    public function overtime(): BelongsTo
    {
        return $this->belongsTo(Overtime::class, 'overtime_id', 'id');
    }

    /**
     * Get the employee that performed the approval.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
