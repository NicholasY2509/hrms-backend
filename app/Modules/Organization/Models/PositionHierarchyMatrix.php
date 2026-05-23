<?php

namespace App\Modules\Organization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionHierarchyMatrix extends Model
{
    protected $table = 'position_hierarchy_matrix';
    protected $guarded = ['id'];

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'work_location_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function workPosition(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class, 'work_position_id', 'id');
    }

    public function supervisorWorkPosition(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class, 'supervisor_work_position_id', 'id');
    }
}
