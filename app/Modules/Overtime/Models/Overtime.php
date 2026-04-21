<?php

namespace App\Modules\Overtime\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Overtime extends Model
{
    use SoftDeletes;

    protected $table = 'overtimes';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    const TYPE_GENERAL = 'UMUM';
    const TYPE_DAC = 'DAC';
    const TYPE_HOLIDAY = 'NATIONAL';

    /**
     * Get the employee that owns the Overtime.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the overtime_type that owns the Overtime.
     */
    public function overtime_type(): BelongsTo
    {
        return $this->belongsTo(OvertimeType::class, 'overtime_type_id', 'id');
    }

    /**
     * Get the approvals for the Overtime.
     */
    public function overtime_approvals(): HasMany
    {
        return $this->hasMany(OvertimeApproval::class, 'overtime_id', 'id');
    }

    /**
     * Get the attachments for the Overtime.
     */
    public function overtime_attachments(): HasMany
    {
        return $this->hasMany(OvertimeAttachment::class, 'overtime_id', 'id');
    }

    /**
     * Get the dynamic status of the overtime request (Legacy Port).
     */
    public function getStatusAttribute()
    {
        if ($this->settled_at) {
            return 'Settled';
        }

        // Prioritize Rejected status
        $rejection = $this->overtime_approvals()
            ->where('status', 'Rejected')
            ->first();

        if ($rejection) {
            return "Rejected";
        }

        $status = 'Pending';

        // Check for specific approval record matching the basic status
        $approval = $this->overtime_approvals()
            ->where('status', $status)
            ->first();

        // If not found, get the most recent approval
        if (!$approval) {
            $approval = $this->overtime_approvals()
                ->orderByDesc('updated_at')
                ->first();
        } else {
            return "Pending";
        }

        return $status;
    }
}
