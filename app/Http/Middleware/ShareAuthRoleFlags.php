<?php

namespace App\Http\Middleware;

use App\Domain\Authz\PermissionService;
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
            $roleNames = app(PermissionService::class)
                ->roleNames(Auth::user())
                ->all();
        }

        $roleSet = array_fill_keys($roleNames, true);
        $flags = [];

        foreach (self::ROLE_NAMES as $roleName) {
            $flags['is'.$roleName] = isset($roleSet[$roleName]);
        }

        $canPerm = [];
        $user = Auth::user();
        if ($user) {
            $permissions = app(PermissionService::class);
            foreach (array_keys(config('permissions.keys', [])) as $key) {
                $canPerm[$key] = $this->canPerm($permissions, $user, $key, $roleSet);
            }
        }

        View::share(array_merge([
            'authRoles' => $roleNames,
            'authRoleSet' => $roleSet,
            'canPerm' => $canPerm,
            'permissionsEnforce' => (bool) config('permissions.enforce'),
        ], $flags));

        return $next($request);
    }

    /**
     * @param  array<string, true>  $roleSet
     */
    private function canPerm(PermissionService $permissions, $user, string $key, array $roleSet): bool
    {
        if (isset($roleSet['SuperAdmin'])) {
            return true;
        }

        if (config('permissions.enforce')) {
            return $permissions->userCan($user, $key);
        }

        foreach (config('permissions.seed', []) as $role => $keys) {
            if (isset($roleSet[$role]) && in_array($key, $keys, true)) {
                return true;
            }
        }

        return false;
    }
}
