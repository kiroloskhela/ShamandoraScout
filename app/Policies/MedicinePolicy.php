<?php

namespace App\Policies;

use App\Models\User;

/**
 * Medicine inventory: SuperAdmin or AdminFirstAid.
 */
class MedicinePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasMedicineRole($user);
    }

    public function manage(User $user): bool
    {
        return $this->hasMedicineRole($user);
    }

    public function dispense(User $user): bool
    {
        return $this->hasMedicineRole($user);
    }

    private function hasMedicineRole(User $user): bool
    {
        return $user->role()->whereIn('RoleName', ['SuperAdmin', 'AdminFirstAid'])->exists();
    }
}
