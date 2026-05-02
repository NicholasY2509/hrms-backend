<?php

namespace App\Modules\Career\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerType extends Model
{
    use SoftDeletes;

    protected $table = 'career_types';
    protected $guarded = ['id'];
}
