<?php

namespace App\Modules\Employee\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FaceRecognitionService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected \App\Modules\Employee\Repositories\UserFaceProfileRepository $repository;

    public function __construct(\App\Modules\Employee\Repositories\UserFaceProfileRepository $repository)
    {
        $this->baseUrl = config('services.face_api.url');
        $this->apiKey = config('services.face_api.key');
        $this->repository = $repository;
    }

    /**
     * Get face profile by user ID.
     *
     * @param int $userId
     * @return \App\Modules\Employee\Models\UserFaceProfile|null
     */
    public function getProfile(int $userId): ?\App\Modules\Employee\Models\UserFaceProfile
    {
        return $this->repository->findByUserId($userId);
    }

    /**
     * Register a new face profile and save it to the database.
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

        $request = Http::withToken($this->apiKey)->asMultipart();

        $request->attach('user_id', $userId);
        $request->attach('liveness_passed', $livenessPassed ? '1' : '0');

        foreach ($images as $image) {
            $request->attach(
                'images',
                fopen($image->path(), 'r'),
                $image->getClientOriginalName()
            );
        }

        $url = "{$this->baseUrl}/face/register";

        $response = $request->post($url);

        if ($response->failed()) {
            throw new Exception($response->json('message') ?? 'Face registration failed');
        }

        $result = $response->json();

        if ($result['success'] && ($result['face_detected'] ?? false)) {
            $this->repository->updateOrCreate(
                ['user_id' => $userId],
                [
                    'embedding' => $result['embedding'],
                    'registered_at' => now(),
                ]
            );
        }

        return $result;
    }

    /**
     * Verify a face against a stored embedding.
     *
     * @param int $userId
     * @param \Illuminate\Http\UploadedFile $image
     * @param float|null $threshold
     * @return array
     * @throws Exception
     */
    public function verify(int $userId, $image, ?float $threshold = null): array
    {
        $profile = $this->getProfile($userId);

        if (!$profile) {
            return [
                'success' => false,
                'message' => 'Face profile not enrolled',
                'matched' => false
            ];
        }

        $request = Http::withToken($this->apiKey)->asMultipart();

        $request->attach('user_id', $userId);
        $request->attach('stored_embedding', json_encode($profile->embedding));

        if ($threshold !== null) {
            $request->attach('threshold', $threshold);
        }

        $request->attach(
            'image',
            fopen($image->path(), 'r'),
            $image->getClientOriginalName()
        );

        $url = "{$this->baseUrl}/face/verify";

        $response = $request->post($url);

        if ($response->failed()) {
            throw new Exception($response->json('message') ?? 'Face verification failed');
        }

        return $response->json();
    }
}
