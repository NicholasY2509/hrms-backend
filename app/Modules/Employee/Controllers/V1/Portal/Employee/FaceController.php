<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Employee;

/**
 * @group Face Recognition
 * @subgroup Employee Portal
 */

use App\Http\Controllers\Controller;
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
        $profile = $this->faceService->getProfile($userId);

        return $this->successResponse([
            'is_enrolled' => !is_null($profile),
            'can_change' => $profile?->can_change ?? false,
            'enrolled_at' => $profile?->created_at?->format('Y-m-d H:i:s'),
        ], 'Face profile status retrieved');
    }

    /**
     * Register new face biometric data.
     *
     * @bodyParam images array required List of image files.
     * @bodyParam liveness_passed boolean required Whether liveness check passed.
     *
     * @response {
     *  "status": "Success",
     *  "message": "Face registered successfully",
     *  "data": {
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

            if ($result['success'] && ($result['face_detected'] ?? false)) {
                return $this->successResponse([
                    'face_detected' => true,
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
    public function verify(\App\Modules\Employee\Requests\FaceVerifyRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $userId = Auth::id();

            $result = $this->faceService->verify(
                $userId,
                $request->file('image'),
                max($data['threshold'] ?? 0.8, 0.8)
            );

            if ($result['success'] && ($result['matched'] ?? false)) {
                return $this->successResponse([
                    'matched' => true,
                    'similarity_score' => $result['similarity_score'] ?? null,
                ], 'Face verified successfully');
            }

            // Return 404 if profile not enrolled, which is now handled by the service message
            $statusCode = ($result['message'] ?? '') === 'Face profile not enrolled' ? 404 : 422;

            return $this->errorResponse(
                $result['message'] ?? 'Face verification failed',
                $statusCode
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
