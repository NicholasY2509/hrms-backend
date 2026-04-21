<?php

namespace App\Modules\Employee\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FaceRecognitionService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.face_api.url');
        $this->apiKey = config('services.face_api.key');
    }

    /**
     * Register a new face profile.
     *
     * @param int $userId
     * @param array $images Base64 encoded images
     * @param string|null $video Base64 encoded video
     * @return array
     * @throws Exception
     */
    public function register(int $userId, array $images, ?string $video = null): array
    {
        $response = Http::withToken($this->apiKey)->post("{$this->baseUrl}/face/register", [
            'user_id' => $userId,
            'images' => $images,
            'video' => $video,
        ]);

        if ($response->failed()) {
            Log::error('Face Registration Failed', [
                'user_id' => $userId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception($response->json('message') ?? 'Face registration failed');
        }

        return $response->json();
    }

    /**
     * Verify a face against a stored embedding.
     *
     * @param int $userId
     * @param string $image Base64 encoded image
     * @param array $storedEmbedding
     * @param string|null $video Base64 encoded video
     * @return array
     * @throws Exception
     */
    public function verify(int $userId, string $image, array $storedEmbedding, ?string $video = null): array
    {
        $response = Http::withToken($this->apiKey)->post("{$this->baseUrl}/face/verify", [
            'user_id' => $userId,
            'image' => $image,
            'stored_embedding' => $storedEmbedding,
            'video' => $video,
        ]);

        if ($response->failed()) {
            Log::error('Face Verification Failed', [
                'user_id' => $userId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception($response->json('message') ?? 'Face verification failed');
        }

        return $response->json();
    }
}
