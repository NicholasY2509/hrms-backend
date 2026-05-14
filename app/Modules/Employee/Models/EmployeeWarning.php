<?php

namespace App\Modules\Employee\Models;

use App\Modules\Disciplinary\Models\WarningLetterType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWarning extends Model
{
    use SoftDeletes;

    protected $table = 'warning_letters';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function warning_letter_type(){
        return $this->belongsTo(WarningLetterType::class);
    }
}
