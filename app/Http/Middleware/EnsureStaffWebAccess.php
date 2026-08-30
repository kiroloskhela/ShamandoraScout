<?php

namespace App\Http\Middleware;

use App\Domain\Authz\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blade UI is staff-only. Mkhdom and other non-staff sessions are dropped.
 */
class EnsureStaffWebAccess
{
    public function __construct(private PermissionService $permissions) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        if ($this->permissions->isStaff($request->user())) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login-auth')
            ->withErrors([
                'login' => __('These credentials do not match our records.'),
            ]);
    }
}
