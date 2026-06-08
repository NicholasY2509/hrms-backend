<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PassportApiService
{
    protected string $baseUrl;
    protected ?string $apiToken;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.passport.url'), '/');
        $this->apiToken = config('services.passport.token');
    }

    /**
     * Create a user in the Passport system.
     *
     * @param array $payload
     * @return array|null The created user data from Passport, or null on failure.
     */
    public function createUser(array $payload): ?array
    {
        // Get the backend service token
        $token = $this->apiToken;

        if (!$token) {
            Log::warning('[PassportApiService] No PASSPORT_API_TOKEN found in .env.');
        }

        Log::info('[PassportApiService] Attempting to create user in Passport.', [
            'url' => $this->baseUrl . '/api/v1/server/users',
            'payload_keys' => array_keys($payload), // Log keys to avoid exposing passwords in plain text if possible, but we'll log it in error if it fails
        ]);

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->post($this->baseUrl . '/api/v1/server/users', $payload);

            if ($response->successful()) {
                Log::info('[PassportApiService] Successfully created user in Passport.', [
                    'payload' => $payload,
                    'response' => $response->json('data'),
                ]);
                return $response->json('data');
            }

            Log::error('[PassportApiService] Failed to create user in Passport.', [
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload,
            ]);

        } catch (\Exception $e) {
            Log::error('[PassportApiService] Exception creating user in Passport: ' . $e->getMessage(), [
                'payload' => $payload,
            ]);
        }

        return null;
    }

    /**
     * Get list of clients from Passport.
     */
    public function getClients()
    {
        $token = $this->apiToken;

        Log::info('[PassportApiService] Attempting to fetch clients from Passport.', [
            'url' => $this->baseUrl . '/api/v1/server/clients',
        ]);

        try {
            $response = Http::withToken($token)->acceptJson()->get($this->baseUrl . '/api/v1/server/clients');
            if ($response->successful()) {
                Log::info('[PassportApiService] Successfully fetched clients from Passport.');
                return $response->json();
            }
            Log::error('[PassportApiService] Failed to fetch clients from Passport.', [
                'status' => $response->status(),
                'response' => $response->body(),
                'url' => $this->baseUrl . '/api/v1/server/clients',
            ]);
        } catch (\Exception $e) {
            Log::error('[PassportApiService] getClients exception: ' . $e->getMessage(), [
                'url' => $this->baseUrl . '/api/v1/server/clients',
            ]);
        }
        return ['data' => [], 'status' => false];
    }

    /**
     * Get list of roles from Passport, optionally filtered by client_id.
     */
    public function getRoles($clientId = null)
    {
        $token = $this->apiToken;
        $query = $clientId ? '?client_id=' . $clientId : '';

        Log::info('[PassportApiService] Attempting to fetch roles from Passport.', [
            'url' => $this->baseUrl . '/api/v1/server/roles' . $query,
            'client_id' => $clientId,
        ]);

        try {
            $response = Http::withToken($token)->acceptJson()->get($this->baseUrl . '/api/v1/server/roles' . $query);
            if ($response->successful()) {
                Log::info('[PassportApiService] Successfully fetched roles from Passport.', ['client_id' => $clientId]);
                return $response->json();
            }
            Log::error('[PassportApiService] Failed to fetch roles from Passport.', [
                'status' => $response->status(),
                'response' => $response->body(),
                'url' => $this->baseUrl . '/api/v1/server/roles' . $query,
                'client_id' => $clientId,
            ]);
        } catch (\Exception $e) {
            Log::error('[PassportApiService] getRoles exception: ' . $e->getMessage(), [
                'url' => $this->baseUrl . '/api/v1/server/roles' . $query,
                'client_id' => $clientId,
            ]);
        }
        return ['data' => [], 'status' => false];
    }
}
