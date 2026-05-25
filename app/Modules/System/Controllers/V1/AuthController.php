<?php

namespace App\Modules\System\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\System\Resources\UserResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * @group Authentication
 *
 * API for authentication and identity.
 */
class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Get the authenticated user's account details.
     *
     * @response {
     *  "status": "Success",
     *  "message": "User details retrieved",
     *  "data": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "is_linked_to_employee": true,
     *      "employee_id": 101,
     *      "profileUrl": "https://storage.googleapis.com/bucket/avatars/john.jpg"
     *  }
     * }
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $cachedData = Cache::remember('auth_me_user_' . $user->id, 3600, function () use ($user) {
            $user->load(['employee', 'user_employee']);
            $resource = new UserResource($user);
            return $resource->resolve();
        });

        return $this->successResponse($cachedData, 'User details retrieved');
    }
}
