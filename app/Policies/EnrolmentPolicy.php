<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * New enrolments admin: role gate + AdminQetaa scoped to shared sector.
 * Migration is SuperAdmin-only.
 */
class EnrolmentPolicy
{
    private const MANAGE_ROLES = [
        'SuperAdmin',
        'AdminQetaa',
        'AdminSecretary',
        'Secretary',
        'AdminFinance',
    ];

    private const UNSCOPED_ROLES = [
        'SuperAdmin',
        'AdminSecretary',
        'Secretary',
        'AdminFinance',
    ];

    public function viewAny(User $user): bool
    {
        return $this->hasAnyRole($user, self::MANAGE_ROLES);
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
        return $user->role()->where('RoleName', 'SuperAdmin')->exists();
    }

    private function canAccessEnrolment(User $user, int $newUserPersonId): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($this->hasAnyRole($user, self::UNSCOPED_ROLES)) {
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

    /**
     * @param  list<string>  $roles
     */
    private function hasAnyRole(User $user, array $roles): bool
    {
        return $user->role()->whereIn('RoleName', $roles)->exists();
    }
}
