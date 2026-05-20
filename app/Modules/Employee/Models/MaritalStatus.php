<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;

class MaritalStatus extends Model
{
    protected $table = 'marital_statuses';
    protected $guarded = ['id'];
    public $timestamps = false;
}
