<?php

namespace App\Modules\Organization\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkPosition extends Model
{
    use SoftDeletes;

    protected $table = 'work_positions';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? false;

        $query->when($search, function ($query, $search) {
            $search = preg_replace('/\s+/', ' ', trim($search));
            $query->where(function ($query) use ($search) {
                $query->where('alias', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
            });
        });
    }

    public function criteria()
    {
        return $this->hasMany(WorkPositionCriteria::class, 'work_position_id');
    }

    public function approvals()
    {
        return $this->hasMany(WorkPositionApproval::class, 'work_position_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'work_position_id', 'id');
    }
}
