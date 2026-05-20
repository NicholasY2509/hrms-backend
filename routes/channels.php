<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['api.auth']]);

Broadcast::channel('reports.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('tasks.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('activities.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('App.Modules.User.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
