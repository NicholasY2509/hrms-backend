<?php

use App\Http\Middleware\RequireRole;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SyncUserByEmail;
use App\Http\Middleware\UnifiedApiAuth;
use App\Http\Middleware\VerifyLegacySignature;
use App\Modules\System\Models\Task;
use Illuminate\Console\Scheduling\Event;
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
        $trackTask = function (Event $event, string $type) {
            $taskId = null;
            $event->before(function () use (&$taskId, $type) {
                $task = Task::create([
                    'type' => $type,
                    'status' => 'processing',
                    'message' => 'Scheduled command started',
                    'user_id' => null,
                ]);
                $taskId = $task->id;
            })->onSuccess(function () use (&$taskId) {
                if ($taskId) {
                    Task::where('id', $taskId)->update([
                        'status' => 'completed',
                        'message' => 'Scheduled command executed successfully',
                        'progress' => 100,
                        'completed_at' => now(),
                    ]);
                }
            })->onFailure(function () use (&$taskId) {
                if ($taskId) {
                    Task::where('id', $taskId)->update([
                        'status' => 'failed',
                        'message' => 'Scheduled command failed',
                        'completed_at' => now(),
                    ]);
                }
            });
            return $event;
        };

        $trackTask($schedule->command('activitylog:clean')->daily(), 'activitylog:clean');

        // Automated Request Cleanup
        $trackTask($schedule->command('approval:auto-reject-stale')->dailyAt('00:00'), 'approval:auto-reject-stale');

        // Critical Business Rules
        $trackTask($schedule->command('leave:grant-monthly')->lastDayOfMonth('00:00'), 'leave:grant-monthly');
        $trackTask($schedule->command('attendance:daily-absence-penalty')->dailyAt('00:05')->timezone('Asia/Jakarta'), 'attendance:daily-absence-penalty');

        // Notification Alerts
        $trackTask($schedule->command('employee:notify-birthdays')->dailyAt('08:00')->timezone('Asia/Jakarta'), 'employee:notify-birthdays');
        $trackTask($schedule->command('attendance:notify-missing-logs')->dailyAt('09:00')->timezone('Asia/Jakarta'), 'attendance:notify-missing-logs');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
