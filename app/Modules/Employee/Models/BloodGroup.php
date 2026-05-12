<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;

class BloodGroup extends Model
{
    protected $table = 'blood_groups';
    protected $guarded = ['id'];
    public $timestamps = false;
}
