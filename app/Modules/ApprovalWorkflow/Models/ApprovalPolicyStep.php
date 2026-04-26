<?php

namespace App\Modules\ApprovalWorkflow\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalPolicyStep extends Model
{
    protected $table = 'approval_policy_steps';
    protected $guarded = ['id'];

    /**
     * Parent policy.
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(ApprovalPolicy::class, 'approval_policy_id');
    }

    /**
     * Get the target group if type is 'group'.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ApprovalGroup::class, 'target_id');
    }

    /**
     * Get the target employee if type is 'employee'.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'target_id');
    }
}
