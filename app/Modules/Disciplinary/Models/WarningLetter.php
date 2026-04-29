<?php

namespace App\Modules\Disciplinary\Models;

use App\Modules\Employee\Models\Employee;
use App\Traits\Approvable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarningLetter extends Model
{
    use SoftDeletes, Approvable;

    protected $table = 'warning_letters';
    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
