<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['api.auth']]);

Broadcast::channel('reports.{userId}', function ($user, $userId) {
    return true;
});

Broadcast::channel('tasks.{userId}', function ($user, $userId) {
    return true;
});

Broadcast::channel('activities.{userId}', function ($user, $userId) {
    return true;
});
