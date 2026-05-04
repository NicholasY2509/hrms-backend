<?php

namespace App\Http\Middleware;

use App\Modules\User\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyLegacySignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Legacy-Signature');
        $timestamp = $request->header('X-Legacy-Timestamp');
        $systemKey = $request->header('X-Legacy-Key');
        $userEmail = $request->header('X-Legacy-User-Email');

        if (!$signature && !$timestamp && !$systemKey) {
            // No legacy headers, pass through
            return $next($request);
        }

        if (!$signature || !$timestamp || !$systemKey) {
            return $this->unauthorized('Partial legacy authentication headers provided');
        }

        // 1. Validate System Key
        $allowedKey = config('services.legacy_system.key');
        $secret = config('services.legacy_system.secret');

        if (!$allowedKey || $systemKey !== $allowedKey) {
            return $this->unauthorized('Invalid system key');
        }

        // 2. Validate Timestamp (allow 5 minutes drift)
        if (abs(time() - (int)$timestamp) > 300) {
            return $this->unauthorized('Request expired or clock drift too high');
        }

        // 3. Verify Signature
        $method = strtoupper($request->getMethod());
        $path = '/' . ltrim($request->getPathInfo(), '/');
        $body = $request->getContent();
        
        $dataToSign = $timestamp . $method . $path . $body;
        $expectedSignature = hash_hmac('sha256', $dataToSign, $secret);

        Log::debug("Legacy Auth Verification Attempt", [
            'timestamp' => $timestamp,
            'method' => $method,
            'path' => $path,
            'body_length' => strlen($body),
            'data_to_sign' => $dataToSign,
            'expected_signature' => $expectedSignature,
            'received_signature' => $signature
        ]);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning("Legacy Auth Failed: Signature mismatch", [
                'expected' => $expectedSignature,
                'received' => $signature,
                'timestamp' => $timestamp,
                'method' => $method,
                'path' => $path,
                'body_length' => strlen($body),
                'body_preview' => substr($body, 0, 100),
                'data_to_sign' => $dataToSign,
                'headers' => $request->headers->all(),
            ]);
            return $this->unauthorized('Invalid signature');
        }

        // 4. Authenticate User if provided
        if ($userEmail) {
            $user = User::where('email', $userEmail)->first();
            if ($user) {
                Auth::setUser($user);
                Auth::guard('api')->setUser($user);
                $request->setUserResolver(fn() => $user);
            } else {
                Log::warning("Legacy Auth: User not found for email: {$userEmail}");
            }
        }

        return $next($request);
    }

    protected function unauthorized(string $message): Response
    {
        return response()->json([
            'status' => 'Error',
            'message' => $message,
            'data' => null
        ], 401);
    }
}
