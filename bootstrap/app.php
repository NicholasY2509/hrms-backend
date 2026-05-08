<?php

use App\Http\Middleware\RequireRole;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SyncUserByEmail;
use App\Http\Middleware\UnifiedApiAuth;
use App\Http\Middleware\VerifyLegacySignature;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\ThrottleRequests;

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
        $middleware->append(SecurityHeaders::class);
        $middleware->throttleApi('api');
        
        $middleware->alias([
            'throttle.auth' => ThrottleRequests::class . ':auth',
            'api.auth' => UnifiedApiAuth::class,
            'legacy.auth' => VerifyLegacySignature::class,
            'role' => RequireRole::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('activitylog:clean')->daily();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
