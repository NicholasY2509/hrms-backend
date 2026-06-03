<?php

namespace App\Modules\System\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Organization\Models\WorkPosition;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PassportRole extends Model
{
    protected $fillable = [
        'passport_role_id',
        'passport_client_id',
        'name',
        'is_global',
    ];

    protected $casts = [
        'is_global' => 'boolean',
    ];

    protected $appends = ['client_name'];

    public function workPositions(): BelongsToMany
    {
        return $this->belongsToMany(WorkPosition::class, 'work_position_passport_role', 'passport_role_id', 'work_position_id');
    }

    public function client()
    {
        return $this->belongsTo(PassportClient::class, 'passport_client_id', 'passport_client_id');
    }

    public function getClientNameAttribute()
    {
        return $this->client ? $this->client->name : 'Unknown Client';
    }
}
