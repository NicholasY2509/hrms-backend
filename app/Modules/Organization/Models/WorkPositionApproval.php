<?php

namespace App\Modules\Organization\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPositionApproval extends Model
{
    protected $table = 'work_position_approvals';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'interviewer_id', 'id');
    }

    public function work_position(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class, 'work_position_id', 'id');
    }

    public function interviewer_position(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class, 'interviewer_position_id', 'id');
    }
}
