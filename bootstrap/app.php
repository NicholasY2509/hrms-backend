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
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
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
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
        ...glob(__DIR__.'/../app/Modules/*/Console/Commands'),
    ])
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

        // Automated Request Cleanup
        $schedule->command('approval:auto-reject-stale')->dailyAt('00:00');

        // Critical Business Rules
        $schedule->command('leave:grant-monthly')->lastDayOfMonth('00:00');
        $schedule->command('attendance:daily-absence-penalty')->dailyAt('00:05')->timezone('Asia/Jakarta');

        // Notification Alerts
        $schedule->command('employee:notify-birthdays')->dailyAt('08:00')->timezone('Asia/Jakarta');
        $schedule->command('attendance:notify-missing-logs')->dailyAt('09:00')->timezone('Asia/Jakarta');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
