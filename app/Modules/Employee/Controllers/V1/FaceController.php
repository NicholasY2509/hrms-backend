<?php

namespace App\Modules\Employee\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\UserFaceProfile;
use App\Modules\Employee\Requests\FaceRegisterRequest;
use App\Modules\Employee\Services\FaceRecognitionService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * @group Face Recognition
 *
 * API for managing face biometric profiles.
 */
class FaceController extends Controller
{
    use ApiResponses;

    protected FaceRecognitionService $faceService;

    public function __construct(FaceRecognitionService $faceService)
    {
        $this->faceService = $faceService;
    }

    /**
     * Get the face profile status of the authenticated user.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Face profile status retrieved",
     *  "data": {
     *      "is_enrolled": true,
     *      "enrolled_at": "2026-04-21 12:00:00"
     *  }
     * }
     */
    public function status(): JsonResponse
    {
        $userId = Auth::id();
        $profile = UserFaceProfile::where('user_id', $userId)->first();

        return $this->successResponse([
            'is_enrolled' => !is_null($profile),
            'enrolled_at' => $profile?->created_at?->format('Y-m-d H:i:s'),
        ], 'Face profile status retrieved');
    }

    /**
     * Register new face biometric data.
     *
     * @bodyParam images array required List of Base64 encoded images.
     * @bodyParam video string Base64 encoded video for liveness detection.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Face registered successfully",
     *  "data": {
     *      "liveness_passed": true,
     *      "face_detected": true
     *  }
     * }
     */
    public function register(FaceRegisterRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $data = $request->validated();

            $result = $this->faceService->register(
                $userId,
                $data['images'],
                $data['video'] ?? null
            );

            if ($result['success'] && $result['liveness_passed']) {
                UserFaceProfile::updateOrCreate(
                    ['user_id' => $userId],
                    ['embedding' => $result['embedding']]
                );

                return $this->successResponse([
                    'liveness_passed' => true,
                    'face_detected' => $result['face_detected'],
                ], 'Face registered successfully');
            }

            return $this->errorResponse(
                $result['message'] ?? 'Liveness detection failed',
                422
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
