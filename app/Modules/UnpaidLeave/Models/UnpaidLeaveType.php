<?php

namespace App\Modules\UnpaidLeave\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnpaidLeaveType extends Model
{
    use SoftDeletes;

    protected $table = 'unpaid_leave_types';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
