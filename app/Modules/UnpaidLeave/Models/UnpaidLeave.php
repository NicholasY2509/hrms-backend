<?php

namespace App\Modules\UnpaidLeave\Models;

use App\Modules\Employee\Models\Employee;
use App\Modules\ApprovalWorkflow\Traits\HasApprovalStatus;
use App\Modules\UnpaidLeave\Services\UnpaidLeaveService;
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
     * Scope a query to apply filters.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['start_date'] ?? null, function ($q, $startDate) {
            $q->whereDate('start_date', '>=', $startDate);
        });

        $query->when($filters['end_date'] ?? null, function ($q, $endDate) {
            $q->whereDate('end_date', '<=', $endDate);
        });

        $query->when($filters['settle_status'] ?? null, function ($q, $status) {
            if ($status === 'settled') {
                $q->whereNotNull('settled_at');
            } elseif ($status === 'unsettled') {
                $q->whereNull('settled_at');
            }
        });

        $query->when($filters['status'] ?? null, function ($q, $status) {
            $q->whereHas('approvalRequest', function ($sq) use ($status) {
                $sq->where('status', $status);
            });
        });

        $query->when($filters['department_ids'] ?? null, function ($q, $departmentIds) {
            $departmentIds = is_array($departmentIds) ? $departmentIds : explode(',', $departmentIds);
            $q->whereHas('employee', function ($sq) use ($departmentIds) {
                $sq->whereIn('department_id', $departmentIds);
            });
        });

        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->whereHas('employee', function ($sq) use ($search) {
                $sq->where(function ($ssq) use ($search) {
                    $ssq->where('employee_id_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        });

        $query->when($filters['employee_id'] ?? null, function ($q, $employeeId) {
            $q->where('employee_id', $employeeId);
        });

        $query->when($filters['unpaid_leave_type_id'] ?? null, function ($q, $typeId) {
            $q->where('unpaid_leave_type_id', $typeId);
        });

        return $query;
    }

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

    /**
     * Sync the approval status with the model's native fields.
     * Overrides the default trait behavior to include complex settlement logic.
     */
    public function syncApprovalStatus(string $status): void
    {
        if ($status === 'approved') {
            app(UnpaidLeaveService::class)->settle($this);
        }
    }
}
