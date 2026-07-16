<?php

namespace App\Policies;

use App\Models\User;

/**
 * Curricula: any authenticated user may view/download; only SuperAdmin may mutate.
 */
class CurriculaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function delete(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->role()->where('RoleName', 'SuperAdmin')->exists();
    }
}
