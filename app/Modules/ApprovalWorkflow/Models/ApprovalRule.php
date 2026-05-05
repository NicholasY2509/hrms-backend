<?php

namespace App\Modules\ApprovalWorkflow\Models;

use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\WorkLocation;
use App\Modules\Organization\Models\WorkPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRule extends Model
{
    protected $table = 'approval_rules';
    protected $guarded = ['id'];

    /**
     * Steps defined for this rule.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalRuleStep::class, 'approval_rule_id')->orderBy('sequence');
    }

    /**
     * Relationship to the Work Position.
     */
    public function workPosition(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class, 'work_position_id');
    }

    /**
     * Relationship to the Work Location.
     */
    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'work_location_id');
    }

    /**
     * Relationship to the Department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Relationship to the parent Scheme.
     */
    public function scheme(): BelongsTo
    {
        return $this->belongsTo(ApprovalScheme::class, 'approval_scheme_id');
    }
}
