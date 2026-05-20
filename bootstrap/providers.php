<?php

use App\Modules\Notifications\Providers\NotificationsServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\ModuleServiceProvider;

return [
    AppServiceProvider::class,
    ModuleServiceProvider::class,
    NotificationsServiceProvider::class,
];
