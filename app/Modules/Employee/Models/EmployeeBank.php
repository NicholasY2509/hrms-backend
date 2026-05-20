<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBank extends Model
{
    use SoftDeletes;

    protected $table = 'employee_bank_accounts';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
