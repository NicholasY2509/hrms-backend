<?php

namespace App\Modules\Employee\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFaceProfile extends Model
{
    protected $table = 'user_face_profiles';

    protected $fillable = [
        'user_id',
        'embedding',
        'registered_at',
        'can_change',
    ];

    protected $casts = [
        'embedding' => 'array',
        'can_change' => 'boolean',
    ];

    /**
     * Get the user that owns the face profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
