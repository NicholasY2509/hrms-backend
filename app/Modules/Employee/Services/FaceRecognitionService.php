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
     * @param array $images Uploaded image files
     * @param bool $livenessPassed Whether liveness check passed
     * @return array
     * @throws Exception
     */
    public function register(int $userId, array $images, bool $livenessPassed): array
    {
        if (!$livenessPassed) {
            throw new Exception('Liveness check failed. Please try again.');
        }

        $multipart = [
            ['name' => 'user_id', 'contents' => $userId],
        ];

        foreach ($images as $image) {
            $multipart[] = [
                'name' => 'images',
                'contents' => fopen($image->path(), 'r'),
                'filename' => $image->getClientOriginalName(),
            ];
        }

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/v1/face/register", $multipart);

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
     * @param \Illuminate\Http\UploadedFile $image
     * @param array $storedEmbedding
     * @param float|null $threshold
     * @return array
     * @throws Exception
     */
    public function verify(int $userId, $image, array $storedEmbedding, ?float $threshold = null): array
    {
        $multipart = [
            ['name' => 'user_id', 'contents' => $userId],
            ['name' => 'stored_embedding', 'contents' => json_encode($storedEmbedding)],
        ];

        if ($threshold !== null) {
            $multipart[] = ['name' => 'threshold', 'contents' => $threshold];
        }

        $multipart[] = [
            'name' => 'image',
            'contents' => fopen($image->path(), 'r'),
            'filename' => $image->getClientOriginalName(),
        ];

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/v1/face/verify", $multipart);

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
