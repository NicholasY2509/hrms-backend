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
        // Note: SyncUserByEmail was already modified to 'pass through' if no token
        return $this->passportAuth->handle($request, function ($request) use ($next) {
            // If Passport didn't set a user, try Legacy Signature
            if (!auth()->check()) {
                return $this->legacyAuth->handle($request, $next);
            }

            return $next($request);
        });
    }
}
