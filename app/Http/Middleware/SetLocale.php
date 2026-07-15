<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? $request->cookie('locale')
            ?? config('app.locale', 'ar');

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'ar';
        }

        App::setLocale($locale);

        $response = $next($request);

        if (method_exists($response, 'withCookie')) {
            $response->withCookie(cookie('locale', $locale, 60 * 24 * 365));
        }

        return $response;
    }
}
