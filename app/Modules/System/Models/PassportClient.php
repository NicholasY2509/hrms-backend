<?php

namespace App\Modules\System\Models;

use Illuminate\Database\Eloquent\Model;

class PassportClient extends Model
{
    protected $fillable = [
        'passport_client_id',
        'name',
    ];
}
