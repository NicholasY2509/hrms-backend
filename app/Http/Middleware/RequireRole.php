<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        return $next($request); // TEMPORARY BYPASS
        $user = auth()->user();
        $userRoles = $user?->remote_roles;

        // Ensure $userRoles is an array
        if (is_string($userRoles)) {
            $userRoles = json_decode($userRoles, true) ?? [];
        }
        $userRoles = (array) ($userRoles ?? []);

        // Bypass check if user has 'IT' role
        if (in_array('IT', $userRoles)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            $trimmedRole = trim($role);
            if (in_array($trimmedRole, $userRoles)) {
                return $next($request);
            }
        }

        return response()->json([
            'status'  => 'Error',
            'message' => 'Forbidden: insufficient role.',
        ], 403);
    }
}
