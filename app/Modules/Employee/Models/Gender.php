<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    protected $table = 'genders';
    protected $guarded = ['id'];
    public $timestamps = false;
}
