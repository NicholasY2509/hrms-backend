<?php

namespace App\Modules\Overtime\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Approvable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use App\Modules\ApprovalWorkflow\Traits\HasApprovalStatus;
use Illuminate\Database\Eloquent\SoftDeletes;

class Overtime extends Model
{
    use Approvable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

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

        $request = $this->approvalRequest;
        
        if (!$request) {
            return 'Pending';
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
     * Scope a query to apply filters.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['employee_id'] ?? null, function ($q, $employeeId) {
            $q->where('employee_id', $employeeId);
        });

        $query->when($filters['type'] ?? null, function ($q, $type) {
            $q->where('type', $type);
        });

        $query->when($filters['department_id'] ?? null, function ($q, $departmentId) {
            $q->whereHas('employee', function ($sq) use ($departmentId) {
                $sq->where('department_id', $departmentId);
            });
        });

        $query->when($filters['start_date'] ?? null, function ($q, $startDate) use ($filters) {
            if (!empty($filters['end_date'])) {
                $q->whereBetween('date', [$startDate, $filters['end_date']]);
            } else {
                $q->whereDate('date', '>=', $startDate);
            }
        });

        $query->when($filters['end_date'] ?? null, function ($q, $endDate) use ($filters) {
            if (empty($filters['start_date'])) {
                $q->whereDate('date', '<=', $endDate);
            }
        });

        if (isset($filters['is_settled'])) {
            $filters['is_settled'] ? $query->whereNotNull('settled_at') : $query->whereNull('settled_at');
        }

        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->whereHas('employee', function ($sq) use ($search) {
                $sq->filter(['search' => $search]);
            });
        });

        return $query;
    }
}
