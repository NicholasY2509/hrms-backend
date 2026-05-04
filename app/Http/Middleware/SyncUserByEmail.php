<?php

namespace App\Http\Middleware;

use App\Services\AuthSyncService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SyncUserByEmail
{
    protected AuthSyncService $authSyncService;

    public function __construct(AuthSyncService $authSyncService)
    {
        $this->authSyncService = $authSyncService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check both Header and Query Parameter
        $userId = $request->header('X-Test-As') ?? $request->query('test_as');

        if (app()->environment('local') && $userId) {
            \Illuminate\Support\Facades\Log::info("Impersonating User ID: {$userId}");
            
            $user = \App\Modules\User\Models\User::find($userId);
            if ($user) {
                Auth::setUser($user);
                $request->setUserResolver(fn() => $user);
                return $next($request);
            }
        }

        $token = $request->bearerToken();

        if ($token) {
            $user = $this->authSyncService->syncUserByToken($token);

            if ($user) {
                Auth::setUser($user);
                $request->setUserResolver(fn() => $user);
                return $next($request);
            }

            return response()->json([
                'status' => 'Error',
                'message' => 'User Sync Failed with provided token',
                'data' => null
            ], 401);
        }

        // No token provided, let next middleware try (e.g., Legacy Auth)
        return $next($request);
    }
}
