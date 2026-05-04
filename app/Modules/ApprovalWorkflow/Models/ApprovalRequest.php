<?php

namespace App\Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequest extends Model
{
    protected $table = 'approval_requests';
    protected $guarded = ['id'];

    /**
     * Get the parent approvable model (UnpaidLeave, Overtime, etc.)
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The rule template used for this request.
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(ApprovalRule::class, 'approval_rule_id');
    }

    /**
     * The snapshot steps for this specific request.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalRequestStep::class, 'approval_request_id')->orderBy('sequence');
    }

    /**
     * Get the current active step.
     */
    public function currentStep()
    {
        return $this->steps()->where('sequence', $this->current_step_sequence)->first();
    }
}
