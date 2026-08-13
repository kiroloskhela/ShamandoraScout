<?php

namespace App\Http\Middleware;

use App\Domain\Authz\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hard SuperAdmin gate. Never skipped by PERMISSIONS_ENFORCE.
 */
class SuperAdminOnly
{
    public function __construct(private PermissionService $permissions)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login-auth');
        }

        if (! $this->permissions->isSuperAdmin($request->user())) {
            return response()->view('unauthorized', [], 403);
        }

        return $next($request);
    }
}
