<?php

namespace App\Modules\Notifications\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Modules\ApprovalWorkflow\Events\ApprovalStepActionable;
use App\Modules\ApprovalWorkflow\Events\ApprovalRequestFinished;
use App\Modules\ApprovalWorkflow\Events\ApprovalRequestCreated;
use App\Modules\Attendance\Events\EmployeeLateArrival;
use App\Modules\Employee\Events\EmployeeBirthday;
use App\Modules\Notifications\Listeners\ApprovalNotificationListener;
use App\Modules\Notifications\Listeners\AttendanceNotificationListener;
use App\Modules\Notifications\Listeners\SocialNotificationListener;

class NotificationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            ApprovalStepActionable::class,
            [ApprovalNotificationListener::class, 'handleStepActionable']
        );

        Event::listen(
            ApprovalRequestFinished::class,
            [ApprovalNotificationListener::class, 'handleRequestFinished']
        );

        Event::listen(
            ApprovalRequestCreated::class,
            [ApprovalNotificationListener::class, 'handleRequestCreated']
        );

        Event::listen(
            EmployeeLateArrival::class,
            [AttendanceNotificationListener::class, 'handleLateArrival']
        );

        Event::listen(
            EmployeeBirthday::class,
            [SocialNotificationListener::class, 'handleBirthday']
        );
    }
}
