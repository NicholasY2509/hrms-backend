<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthSyncService
{
    public function syncUserByToken(string $token): ?User
    {
        $tokenHash = md5($token);
        $cacheKey = "user_auth_{$tokenHash}";
        $cacheDuration = 60 * 60 * 6;

        $cachedUserInfo = Cache::get($cacheKey);
        if ($cachedUserInfo) {
            $user = User::where('email', $cachedUserInfo['email'])->first();
            if ($user) {
                $user->setAttribute('remote_profile', $cachedUserInfo['profile']);
                return $user;
            }
        }

        $remoteData = $this->fetchRemoteUserProfile($token);

        if (!$remoteData || !isset($remoteData['data']['email'])) {
            return null;
        }

        $email = $remoteData['data']['email'];
        
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
                'name' => $remoteData['data']['name'] ?? 'Remote User',
            ]);
        }
        if ($user) {
            Cache::put($cacheKey, [
                'email' => $email,
                'profile' => $remoteData
            ], $cacheDuration);
            
            $user->setAttribute('remote_profile', $remoteData);
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
