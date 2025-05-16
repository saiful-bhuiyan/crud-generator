<?php

use Illuminate\Support\Facades\Broadcast;
use Carbon\Carbon;

Broadcast::channel('online-users', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'last_login' => null,
        'last_seen' => $user->last_seen ? Carbon::parse($user->last_seen)->diffForHumans() : null,
        'ip_address' => $user->ip_address,
        'avatar' => $user->avatar ?? null
    ];
});