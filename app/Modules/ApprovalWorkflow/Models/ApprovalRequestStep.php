<?php

namespace App\Modules\ApprovalWorkflow\Models;

use App\Modules\User\Models\User;
use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequestStep extends Model
{
    protected $table = 'approval_request_steps';
    protected $guarded = ['id'];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    /**
     * The parent approval request.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    /**
     * The resolved user who performed the action.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    /**
     * The target approver (User or Group) resolved during initialization.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    /**
     * The target group resolved during initialization.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ApprovalGroup::class, 'approver_id');
    }

    /**
     * Resolve the approver ID(s).
     * Returns an array of employee IDs for groups/positions, or a single ID for individuals.
     */
    public function getResolvedApproverIds()
    {
        if ($this->approver_type === 'group') {
            return $this->group?->employees->pluck('id')->toArray() ?? [];
        }

        if ($this->approver_type === 'work_position') {
            return Employee::where('work_position_id', $this->approver_id)
                ->where('work_employee_status_id', 1)
                ->pluck('id')
                ->toArray();
        }

        return $this->approver_id;
    }

    /**
     * Resolve the approver name(s).
     * Returns a string of employee names for groups/positions, or a single name for individuals.
     */
    public function getResolvedApproverNames(): ?string
    {
        if ($this->actioned_by) {
            return $this->actor?->name;
        }

        if ($this->approver_type === 'group') {
            return $this->group?->employees->pluck('full_name')->join(', ') ?: 'No members';
        }

        if ($this->approver_type === 'work_position') {
            return Employee::where('work_position_id', $this->approver_id)
                ->where('work_employee_status_id', 1)
                ->pluck('full_name')
                ->join(', ') ?: 'No members';
        }

        return $this->approver?->full_name;
    }
}
