<?php

namespace App\Modules\System\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $table = 'tasks';
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
