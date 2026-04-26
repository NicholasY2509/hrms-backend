<?php

namespace App\Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStepType extends Model
{
    protected $table = 'approval_step_types';
    protected $guarded = ['id'];
}
