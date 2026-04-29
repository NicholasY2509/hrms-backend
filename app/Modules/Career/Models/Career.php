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
}
