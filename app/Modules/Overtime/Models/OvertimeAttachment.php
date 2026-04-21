<?php

namespace App\Modules\Overtime\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeAttachment extends Model
{
    use SoftDeletes;

    protected $table = 'overtime_attachments';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
