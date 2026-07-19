<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('attendance.live.{seasonEventId}', function ($user, $seasonEventId) {
    $roles = $user->role()->pluck('Roles.RoleName')->all();
    $allowed = ['SuperAdmin', 'Secretary', 'AdminSecretary', 'Finance', 'AdminFinance'];

    if (! count(array_intersect($roles, $allowed))) {
        return false;
    }

    return DB::table('SeasonEvent as se')
        ->join('Event as e', 'e.EventID', '=', 'se.EventID')
        ->join('EventType as et', 'et.EventTypeID', '=', 'e.EventTypeID')
        ->where('se.SeasonEventID', (int) $seasonEventId)
        ->where('et.TakesReservation', 1)
        ->exists();
});
