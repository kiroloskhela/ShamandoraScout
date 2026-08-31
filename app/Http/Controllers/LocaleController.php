<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, SetLocale::SUPPORTED, true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);
        App::setLocale($locale);

        $fallback = url('/');
        $previous = url()->previous();
        $bases = array_filter([
            rtrim((string) config('app.url'), '/'),
            rtrim($request->getSchemeAndHttpHost(), '/'),
        ]);
        $target = $fallback;

        if ($previous && $previous !== url()->current()) {
            foreach ($bases as $base) {
                if ($previous === $base || Str::startsWith($previous, $base.'/')) {
                    $target = $previous;
                    break;
                }
            }
        }

        return redirect()
            ->to($target)
            ->withCookie(cookie('locale', $locale, 60 * 24 * 365));
    }
}
