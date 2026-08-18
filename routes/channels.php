<?php

use App\Domain\Authz\PermissionService;
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
    if (! app(PermissionService::class)->userCan($user, 'web.attendance.live')) {
        return false;
    }

    return DB::table('SeasonEvent as se')
        ->join('Event as e', 'e.EventID', '=', 'se.EventID')
        ->join('EventType as et', 'et.EventTypeID', '=', 'e.EventTypeID')
        ->join('SeasonEventFinance as sef', 'sef.SeasonEventID', '=', 'se.SeasonEventID')
        ->where('se.SeasonEventID', (int) $seasonEventId)
        ->where('et.TakesReservation', 1)
        ->exists();
});
