<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\UserFaceProfile;

class UserFaceProfileRepository
{
    /**
     * Find face profile by user ID.
     *
     * @param int $userId
     * @return UserFaceProfile|null
     */
    public function findByUserId(int $userId): ?UserFaceProfile
    {
        return UserFaceProfile::where('user_id', $userId)->first();
    }

    /**
     * Update or create a face profile.
     *
     * @param array $attributes
     * @param array $values
     * @return UserFaceProfile
     */
    public function updateOrCreate(array $attributes, array $values): UserFaceProfile
    {
        return UserFaceProfile::updateOrCreate($attributes, $values);
    }
}
