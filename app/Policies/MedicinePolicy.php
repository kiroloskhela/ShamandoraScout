<?php

namespace App\Policies;

use App\Models\User;

/**
 * Medicine inventory: web.medicine.manage.
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
        return app(\App\Domain\Authz\PermissionService::class)->userCan($user, 'web.medicine.manage');
    }
}
