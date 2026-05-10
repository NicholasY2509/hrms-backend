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
     * Position-specific rules (work_position_id is set).
     */
    public function positionRules(): HasMany
    {
        return $this->hasMany(ApprovalRule::class, 'approval_scheme_id')
            ->whereNotNull('work_position_id')
            ->where('is_default', false);
    }

    /**
     * Department-specific rules (department_id is set, no work_position_id).
     */
    public function departmentRules(): HasMany
    {
        return $this->hasMany(ApprovalRule::class, 'approval_scheme_id')
            ->whereNotNull('department_id')
            ->whereNull('work_position_id')
            ->where('is_default', false);
    }

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $search = preg_replace('/\s+/', ' ', trim($search));
            $q->where(function ($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%")
                  ->orWhere('model_class', 'like', "%{$search}%");
            });
        });

        return $query;
    }
}
