<?php

namespace App\Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApprovalScheme extends Model
{
    protected $table = 'approval_schemes';
    protected $guarded = ['id'];

    /**
     * All rules defined for this scheme (Default and Position-specific).
     */
    public function rules(): HasMany
    {
        return $this->hasMany(ApprovalRule::class, 'approval_scheme_id');
    }

    /**
     * The global default rule for this scheme.
     */
    public function defaultRule(): HasOne
    {
        return $this->hasOne(ApprovalRule::class, 'approval_scheme_id')->where('is_default', true);
    }

    /**
     * Position-specific rules.
     */
    public function positionRules(): HasMany
    {
        return $this->hasMany(ApprovalRule::class, 'approval_scheme_id')->where('is_default', false);
    }
}
