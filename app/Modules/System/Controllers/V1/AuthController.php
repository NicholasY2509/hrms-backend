<?php

namespace App\Modules\System\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Modules\System\Resources\UserResource;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * Login and retrieve a JWT token.
     *
     * @bodyParam email string required The user's email. Example: john@example.com
     * @bodyParam password string required The user's password.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated user's account details.
     */
    public function me(): JsonResponse
    {
        $user = Auth::guard('api')->user();

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

    /**
     * Refresh the current JWT token.
     */
    public function refresh(): JsonResponse
    {
        try {
            return $this->respondWithToken(Auth::guard('api')->refresh());
        } catch (\Exception $e) {
            return $this->errorResponse('Could not refresh token', 401);
        }
    }

    /**
     * Logout and invalidate user cache.
     */
    public function logout(): JsonResponse
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            Cache::forget('auth_me_user_' . $user->id);
        }

        try {
            Auth::guard('api')->logout();
        } catch (\Exception $e) {
            // Token might be already invalid
        }

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Helper function to format the token response.
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'status' => 'Success',
            'message' => 'Token generated successfully',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
            ]
        ]);
    }
}
