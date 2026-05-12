<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;

class Religion extends Model
{
    protected $table = 'religions';
    protected $guarded = ['id'];
    public $timestamps = false;
}
