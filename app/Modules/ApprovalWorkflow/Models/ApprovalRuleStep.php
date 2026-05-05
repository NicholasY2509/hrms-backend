<?php

namespace App\Modules\ApprovalWorkflow\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRuleStep extends Model
{
    protected $table = 'approval_rule_steps';
    protected $guarded = ['id'];

    /**
     * Parent rule.
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(ApprovalRule::class, 'approval_rule_id');
    }

    /**
     * Relationship to Approval Group (if type is 'group').
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ApprovalGroup::class, 'target_id');
    }

    /**
     * Relationship to specific Employee (if type is 'user').
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'target_id');
    }

    /**
     * Relationship to Work Position (if type is 'work_position').
     */
    public function workPosition(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Organization\Models\WorkPosition::class, 'target_id');
    }
}
