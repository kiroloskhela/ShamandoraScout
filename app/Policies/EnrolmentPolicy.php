<?php

namespace App\Policies;

use App\Domain\Authz\PermissionService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * New enrolments admin: capability gate + AdminQetaa scoped to shared sector.
 * Unscoped review and migration are separate keys.
 */
class EnrolmentPolicy
{
    public function viewAny(User $user): bool
    {
        return app(PermissionService::class)->userCan($user, 'web.enrolments.manage');
    }

    public function view(User $user, int $newUserPersonId): bool
    {
        return $this->canAccessEnrolment($user, $newUserPersonId);
    }

    public function update(User $user, int $newUserPersonId): bool
    {
        return $this->canAccessEnrolment($user, $newUserPersonId);
    }

    public function approve(User $user, int $newUserPersonId): bool
    {
        return $this->canAccessEnrolment($user, $newUserPersonId);
    }

    public function delete(User $user, int $newUserPersonId): bool
    {
        return $this->canAccessEnrolment($user, $newUserPersonId);
    }

    public function migrate(User $user): bool
    {
        return app(PermissionService::class)->userCan($user, 'web.enrolments.migrate');
    }

    private function canAccessEnrolment(User $user, int $newUserPersonId): bool
    {
        $permissions = app(PermissionService::class);

        if (! $permissions->userCan($user, 'web.enrolments.manage')) {
            return false;
        }

        if ($permissions->userCan($user, 'web.enrolments.unscoped')) {
            return DB::table('NewUsersInformation')->where('PersonID', $newUserPersonId)->exists();
        }

        $adminQetaas = DB::table('PersonQetaa')
            ->where('PersonID', $user->PersonID)
            ->pluck('QetaaID');

        if ($adminQetaas->isEmpty()) {
            return false;
        }

        return DB::table('NewUsersInformation')
            ->where('PersonID', $newUserPersonId)
            ->whereIn('QetaaID', $adminQetaas)
            ->exists();
    }
}
