<?php

namespace App\Modules\ApprovalWorkflow\Models;

use App\Models\User;
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
}
