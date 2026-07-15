<?php

namespace App\Http\Middleware;

use App\Support\AppSettings;
use Closure;
use Illuminate\Http\Request;

class EnsureLiveFormIsOpen
{
    public function handle(Request $request, Closure $next)
    {
        if (AppSettings::liveformIsOpen()) {
            return $next($request);
        }

        return response()->view('person.liveform-closed', [
            'message' => AppSettings::liveformClosedMessage(),
        ], 503);
    }
}
