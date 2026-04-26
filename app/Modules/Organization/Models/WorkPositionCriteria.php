<?php

namespace App\Modules\Organization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPositionCriteria extends Model
{
    protected $table = 'work_position_criterias';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function work_position(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class);
    }
}
