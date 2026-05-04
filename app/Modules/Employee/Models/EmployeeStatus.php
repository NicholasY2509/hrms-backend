<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeStatus extends Model
{
    use SoftDeletes;

    protected $table = 'employee_statuses';
    protected $guarded = ['id'];
}
