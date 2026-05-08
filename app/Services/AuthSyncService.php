<?php

namespace App\Services;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthSyncService
{
    /**
     * Sync user by token. Tries JWT decode first, falls back to HTTP profile call.
     */
    public function syncUserByToken(string $token): ?User
    {
        $tokenHash = md5($token);
        $cacheKey = "user_auth_{$tokenHash}";
        $cacheDuration = 60 * 60 * 6;

        $cachedUserInfo = Cache::get($cacheKey);
        if ($cachedUserInfo) {
            $user = User::where('email', $cachedUserInfo['email'])->first();
            if ($user) {
                $user->setAttribute('remote_roles', $cachedUserInfo['roles'] ?? []);
                return $user;
            }
        }

        $claims = $this->decodeJwtPayload($token);

        Log::info('AuthSyncService: Decoded JWT Claims', [
            'claims' => $claims
        ]);

        $email = $claims['email'] ?? null;
        $roles = $claims['roles'] ?? [];

        // 3. If JWT doesn't have email, fall back to HTTP profile call
        if (!$email) {
            Log::info('AuthSyncService: No email in JWT, falling back to HTTP profile call.');
            $remoteData = $this->fetchRemoteUserProfile($token);

            if (!$remoteData || !isset($remoteData['data']['email'])) {
                return null;
            }

            $email = $remoteData['data']['email'];

            // Extract roles from remote profile if available
            if (empty($roles) && isset($remoteData['data']['roles'])) {
                $roles = collect($remoteData['data']['roles'])
                    ->pluck('name')
                    ->values()
                    ->toArray();
            }
        }

        // 4. Find or create the local User record by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
                'name' => $claims['name'] ?? $remoteData['data']['name'] ?? 'Remote User',
            ]);
        }

        if ($user) {
            Cache::put($cacheKey, [
                'email' => $email,
                'roles' => $roles,
            ], $cacheDuration);

            $user->setAttribute('remote_roles', $roles);
        }

        return $user;
    }

    /**
     * Fetch user profile from the Auth Server (fallback for old tokens).
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

            Log::error("AuthSyncService: Failed to fetch remote profile. Status: {$response->status()}");
        } catch (\Exception $e) {
            Log::error("AuthSyncService: Exception during remote fetch: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Decode the base64url JWT payload section.
     */
    private function decodeJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        $payload = base64_decode(strtr($parts[1], '-_', '+/'));

        return json_decode($payload, true) ?: null;
    }
}
