<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['api.auth']]);

Broadcast::channel('reports.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
