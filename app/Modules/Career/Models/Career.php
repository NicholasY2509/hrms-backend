<?php

namespace App\Modules\Career\Models;

use App\Modules\Employee\Models\Employee;
use App\Traits\Approvable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Career extends Model
{
    use SoftDeletes, Approvable;

    protected $table = 'careers';
    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function careerType(): BelongsTo
    {
        return $this->belongsTo(CareerType::class);
    }

    public function beforeWorkPosition(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Organization\Models\WorkPosition::class, 'before_work_position_id');
    }

    public function afterWorkPosition(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Organization\Models\WorkPosition::class, 'after_work_position_id');
    }

    public function beforeDepartment(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Department::class, 'before_department_id');
    }

    public function afterDepartment(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Department::class, 'after_department_id');
    }

    public function beforeTeam(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Team::class, 'before_team_id');
    }

    public function afterTeam(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Organization\Models\Team::class, 'after_team_id');
    }
}
