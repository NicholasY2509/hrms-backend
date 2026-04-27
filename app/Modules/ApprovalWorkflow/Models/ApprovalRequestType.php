<?php

namespace App\Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequestType extends Model
{
    protected $fillable = [
        'name',
        'model_class',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
