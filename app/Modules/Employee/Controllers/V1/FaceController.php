<?php

namespace App\Modules\Employee\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\UserFaceProfile;
use App\Modules\Employee\Requests\FaceRegisterRequest;
use App\Modules\Employee\Services\FaceRecognitionService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
                $data['liveness_passed']
            );

            if ($result['success']) {
                UserFaceProfile::updateOrCreate(
                    ['user_id' => $userId],
                    ['embedding' => json_encode($result['embedding'])]
                );

                return $this->successResponse([
                    'face_detected' => $result['face_detected'],
                ], 'Face registered successfully');
            }

            return $this->errorResponse(
                $result['message'] ?? 'Face registration failed',
                422
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Verify face for attendance.
     *
     * @bodyParam image file required Uploaded image file.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Face verified successfully",
     *  "data": {
     *      "matched": true,
     *      "similarity_score": 0.92
     *  }
     * }
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image' => 'required|image|max:10240',
            ]);

            $userId = Auth::id();
            $profile = UserFaceProfile::where('user_id', $userId)->first();

            if (!$profile) {
                return $this->errorResponse('Face profile not enrolled', 404);
            }

            $embedding = json_decode($profile->embedding, true);
            $result = $this->faceService->verify(
                $userId,
                $request->file('image'),
                $embedding,
                0.85
            );

            if ($result['success'] && $result['matched']) {
                return $this->successResponse([
                    'matched' => true,
                    'similarity_score' => $result['similarity_score'],
                ], 'Face verified successfully');
            }

            return $this->errorResponse(
                $result['message'] ?? 'Face verification failed',
                422
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
