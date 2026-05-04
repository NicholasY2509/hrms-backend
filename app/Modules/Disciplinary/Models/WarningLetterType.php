<?php

namespace App\Modules\Disciplinary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarningLetterType extends Model
{
    use SoftDeletes;

    protected $table = 'warning_letter_types';
    protected $guarded = ['id'];
}
