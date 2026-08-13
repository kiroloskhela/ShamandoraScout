<?php

namespace App\Http\Middleware;

use App\Domain\Authz\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function __construct(private PermissionService $permissions) {}

    public function handle(Request $request, Closure $next, ?string $key = null): Response
    {
        // Web dual-run: checkAuth still gates when enforce is off.
        // API never had checkAuth — always evaluate the catalog/seed/matrix.
        if (! config('permissions.enforce') && ! $request->is('api/*')) {
            return $next($request);
        }

        $permission = $key;
        $route = $request->route();
        if ((! is_string($permission) || $permission === '') && $route instanceof Route) {
            $permission = $route->defaults['permission'] ?? $route->parameter('permission');
        }

        if (! is_string($permission) || $permission === '') {
            return $this->deny($request, 'missing_permission_key', 'Forbidden');
        }

        $user = $request->user();
        if (! $user) {
            return $this->unauthenticated($request);
        }

        $allowed = false;
        foreach (explode('|', $permission) as $candidate) {
            if ($candidate !== '' && $this->permissions->userCan($user, $candidate)) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            return $this->deny($request, 'capability_denied', 'This action is unauthorized.');
        }

        return $next($request);
    }

    private function unauthenticated(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['ok' => false, 'code' => 'unauthenticated', 'message' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('login-auth');
    }

    private function deny(Request $request, string $code, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['ok' => false, 'code' => $code, 'message' => $message], 403);
        }

        return response()->view('unauthorized', [], 403);
    }
}
