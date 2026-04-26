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
        $token = $request->bearerToken();

        if ($token) {
            $user = $this->authSyncService->syncUserByToken($token);

            if ($user) {
                Auth::login($user);
                return $next($request);
            }
        }

        return response()->json([
            'status' => 'Error',
            'message' => 'Unauthenticated or User Sync Failed',
            'data' => null
        ], 401);
    }
}
