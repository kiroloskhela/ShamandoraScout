<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldRedirect($request)) {
            return $next($request);
        }

        $canonical = rtrim((string) config('app.url'), '/');
        $path = $request->getPathInfo() ?: '/';
        $query = $request->getQueryString();
        $target = $canonical.$path.($query ? '?'.$query : '');

        return redirect()->away($target, 301);
    }

    private function shouldRedirect(Request $request): bool
    {
        if (! app()->environment('production')) {
            return false;
        }

        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }

        $canonicalHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (! is_string($canonicalHost) || $canonicalHost === '') {
            return false;
        }

        $host = $request->getHost();
        if (in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            return false;
        }

        if ($request->is('health', 'health/*', 'up')) {
            return false;
        }

        return strcasecmp($host, $canonicalHost) !== 0;
    }
}
