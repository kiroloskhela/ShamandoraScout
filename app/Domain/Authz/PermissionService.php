<?php

namespace App\Domain\Authz;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionService
{
    public const VERSION_KEY = 'authz.version';

    /** @var array<int, array<string, true>> */
    private array $requestMemo = [];

    /** @var array<int, bool> */
    private array $superAdminMemo = [];

    public function isSuperAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $id = (int) $user->PersonID;
        if ($id <= 0) {
            return false;
        }

        if (array_key_exists($id, $this->superAdminMemo)) {
            return $this->superAdminMemo[$id];
        }

        $this->superAdminMemo[$id] = DB::table('PersonRole as pr')
            ->join('Roles as r', 'r.RoleID', '=', 'pr.RoleID')
            ->where('pr.PersonID', $id)
            ->where('r.RoleName', 'SuperAdmin')
            ->exists();

        return $this->superAdminMemo[$id];
    }

    public function userCan(?User $user, string $key): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return $this->isKnownKey($key);
        }

        if (! $this->isKnownKey($key) || $this->isNonGrantable($key)) {
            Log::warning('authz.unknown_or_nongrantable_key', ['key' => $key, 'person_id' => $user->PersonID]);

            return false;
        }

        if (! config('permissions.enforce')) {
            return $this->seedFallback($user, $key);
        }

        return isset($this->keysFor($user)[$key]);
    }

    /**
     * @return list<string>
     */
    public function keysForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($this->isSuperAdmin($user)) {
            return array_keys(config('permissions.keys', []));
        }

        return array_keys($this->keysFor($user));
    }

    public function bumpVersion(): int
    {
        $next = $this->version() + 1;
        Cache::forever(self::VERSION_KEY, $next);

        $this->requestMemo = [];
        $this->superAdminMemo = [];

        return $next;
    }

    public function version(): int
    {
        try {
            return (int) Cache::get(self::VERSION_KEY, 1);
        } catch (\Throwable) {
            return 1;
        }
    }

    public function isKnownKey(string $key): bool
    {
        return isset(config('permissions.keys')[$key]);
    }

    public function isNonGrantable(string $key): bool
    {
        return in_array($key, config('permissions.non_grantable', []), true);
    }

    public function isGrantable(string $key): bool
    {
        return $this->isKnownKey($key) && ! $this->isNonGrantable($key);
    }

    /**
     * @return array<string, true>
     */
    private function keysFor(User $user): array
    {
        $id = (int) $user->PersonID;
        if (isset($this->requestMemo[$id])) {
            return $this->requestMemo[$id];
        }

        $roleIds = DB::table('PersonRole')
            ->where('PersonID', $id)
            ->pluck('RoleID')
            ->map(fn ($v) => (int) $v)
            ->all();

        $set = [];
        foreach ($roleIds as $roleId) {
            foreach ($this->keysForRole($roleId) as $key) {
                $set[$key] = true;
            }
        }

        $this->requestMemo[$id] = $set;

        return $set;
    }

    /**
     * @return list<string>
     */
    private function keysForRole(int $roleId): array
    {
        $cacheKey = 'authz.role.'.$roleId.'.'.$this->version();

        try {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        } catch (\Throwable $e) {
            Log::warning('authz.cache_error', ['message' => $e->getMessage()]);

            return [];
        }

        $keys = DB::table('role_permissions')
            ->where('RoleID', $roleId)
            ->pluck('permission_key')
            ->filter(fn ($key) => $this->isGrantable((string) $key))
            ->values()
            ->all();

        try {
            Cache::forever($cacheKey, $keys);
        } catch (\Throwable) {
            // Role keys are already loaded from DB for this request.
        }

        return $keys;
    }

    private function seedFallback(User $user, string $key): bool
    {
        $names = $user->role()->pluck('Roles.RoleName');

        foreach (config('permissions.seed', []) as $role => $keys) {
            if ($names->contains($role) && in_array($key, $keys, true)) {
                return true;
            }
        }

        return false;
    }
}
