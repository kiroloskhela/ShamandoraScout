<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareAuthRoleFlags
{
    /**
     * @var array<int, string>
     */
    private const ROLE_NAMES = [
        'SuperAdmin',
        'Secretary',
        'Media',
        'Inventory',
        'Finance',
        'AdminQetaa',
        'AdminSecretary',
        'AdminInventory',
        'AdminFinance',
        'AdminFirstAid',
    ];

    /**
     * Share authenticated role names and sidebar booleans without per-link role queries.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $roleNames = [];

        if (Auth::check()) {
            $roleNames = Auth::user()
                ->role()
                ->pluck('Roles.RoleName')
                ->filter()
                ->values()
                ->all();
        }

        $roleSet = array_fill_keys($roleNames, true);
        $flags = [];

        foreach (self::ROLE_NAMES as $roleName) {
            $flags['is'.$roleName] = isset($roleSet[$roleName]);
        }

        View::share(array_merge([
            'authRoles' => $roleNames,
            'authRoleSet' => $roleSet,
        ], $flags));

        return $next($request);
    }
}
