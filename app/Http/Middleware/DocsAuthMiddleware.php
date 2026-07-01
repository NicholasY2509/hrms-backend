<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DocsAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Opsional: Jika ingin bypass password di environment local
        // if (app()->environment('local')) {
        //     return $next($request);
        // }

        $user = $request->getUser();
        $password = $request->getPassword();

        $expectedUser = env('DOCS_USER', 'admin');
        $expectedPassword = env('DOCS_PASSWORD', 'admin123');

        if ($user === $expectedUser && $password === $expectedPassword) {
            return $next($request);
        }

        return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic realm="API Documentation"']);
    }
}
