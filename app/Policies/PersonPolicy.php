<?php

namespace App\Policies;

use App\Domain\Authz\PermissionService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Person record access: own PersonID for view/update; elevated for others.
 * Delete is never granted by "self" alone (Mkhdom must not delete themselves).
 */
class PersonPolicy
{
    public function view(User $user, User $person): bool
    {
        if ((int) $user->PersonID === (int) $person->PersonID) {
            return true;
        }

        return $this->elevated($user, $person, 'web.people.view_any', 'web.people.manage');
    }

    public function update(User $user, User $person): bool
    {
        if ((int) $user->PersonID === (int) $person->PersonID) {
            return true;
        }

        $permissions = app(PermissionService::class);
        if ($permissions->isSuperAdmin($person) && ! $permissions->isSuperAdmin($user)) {
            return false;
        }

        return $this->elevated($user, $person, 'web.people.update_any', 'web.people.manage');
    }

    public function delete(User $user, User $person): bool
    {
        if (app(PermissionService::class)->isSuperAdmin($person)) {
            return false;
        }

        return $this->elevated($user, $person, 'web.people.delete_any', 'web.people.manage');
    }

    private function elevated(User $user, User $person, string $globalKey, string $scopedKey): bool
    {
        $permissions = app(PermissionService::class);

        if ($permissions->isSuperAdmin($user) || $permissions->userCan($user, $globalKey)) {
            return true;
        }

        if (! $permissions->userCan($user, $scopedKey)) {
            return false;
        }

        $adminQetaas = DB::table('PersonQetaa')
            ->where('PersonID', $user->PersonID)
            ->pluck('QetaaID');

        if ($adminQetaas->isEmpty()) {
            return false;
        }

        return DB::table('PersonQetaa')
            ->where('PersonID', $person->PersonID)
            ->whereIn('QetaaID', $adminQetaas)
            ->exists();
    }
}
