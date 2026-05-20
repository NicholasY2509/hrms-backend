<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnifiedApiAuth
{
    protected SyncUserByEmail $passportAuth;
    protected VerifyLegacySignature $legacyAuth;

    public function __construct(SyncUserByEmail $passportAuth, VerifyLegacySignature $legacyAuth)
    {
        $this->passportAuth = $passportAuth;
        $this->legacyAuth = $legacyAuth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Try Passport/SyncEmail first
        return $this->passportAuth->handle($request, function ($request) use ($next) {
            if (auth()->check()) {
                return $next($request);
            }

            // Fallback to Legacy Signature
            return $this->legacyAuth->handle($request, function ($request) use ($next) {
                if (auth()->check()) {
                    return $next($request);
                }

                // If both failed or neither provided credentials
                return response()->json([
                    'status' => 'Error',
                    'message' => 'Unauthenticated.',
                    'data' => null
                ], 401);
            });
        });
    }
}
