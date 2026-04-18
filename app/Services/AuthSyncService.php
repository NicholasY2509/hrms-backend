<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthSyncService
{
    /**
     * Synchronize user details using a bearer token.
     *
     * @param string $token
     * @return User|null
     */
    public function syncUserByToken(string $token): ?User
    {
        $remoteData = $this->fetchRemoteUserProfile($token);

        if (!$remoteData || !isset($remoteData['data']['email'])) {
            return null;
        }

        $email = $remoteData['data']['email'];
        $cacheKey = "user_profile_{$email}";
        $cacheDuration = 60 * 60 * 6; // 6 hours

        // Check cache
        $cachedData = Cache::get($cacheKey);
        
        $user = User::where('email', $email)->first();

        if (!$cachedData || !$user) {
            // Ensure local user exists and is updated
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'password' => $user->password ?? bcrypt(\Illuminate\Support\Str::random(16)), // Placeholder
                ]
            );

            // Store remote data in cache
            Cache::put($cacheKey, $remoteData, $cacheDuration);
            $cachedData = $remoteData;
        }

        if ($user && $cachedData) {
            $user->setAttribute('remote_profile', $cachedData);
        }

        return $user;
    }

    /**
     * Fetch user profile from the Auth Server.
     *
     * @param string $token
     * @return array|null
     */
    protected function fetchRemoteUserProfile(string $token): ?array
    {
        $url = rtrim(config('services.auth_server.url'), '/') . '/api/v1/user/profile';

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("AuthSyncService: Failed to fetch remote user profile. Status: {$response->status()} - Body: {$response->body()}");
        } catch (\Exception $e) {
            Log::error("AuthSyncService: Exception during remote fetch. Message: {$e->getMessage()}");
        }

        return null;
    }
}
