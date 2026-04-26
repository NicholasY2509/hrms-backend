<?php

namespace App\Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalPolicy extends Model
{
    protected $table = 'approval_policies';
    protected $guarded = ['id'];

    /**
     * Steps defined for this policy.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalPolicyStep::class, 'approval_policy_id')->orderBy('sequence');
    }
}
