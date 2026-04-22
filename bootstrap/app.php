<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            foreach (glob(base_path('routes/v*.php')) as $file) {
                $version = basename($file, '.php');
                Route::prefix("api/{$version}")
                    ->middleware('api')
                    ->group($file);
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->throttleApi('api');
        
        // Specific rate limiting for authentication endpoints
        $middleware->alias([
            'throttle.auth' => \Illuminate\Routing\Middleware\ThrottleRequests::class . ':auth',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
