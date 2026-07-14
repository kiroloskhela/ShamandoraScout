<?php

namespace App\Policies;

use App\Models\User;

/**
 * Person record access: own PersonID, or SuperAdmin / AdminQetaa.
 */
class PersonPolicy
{
    public function view(User $user, User $person): bool
    {
        return $this->ownsOrElevated($user, $person);
    }

    public function update(User $user, User $person): bool
    {
        return $this->ownsOrElevated($user, $person);
    }

    private function ownsOrElevated(User $user, User $person): bool
    {
        if ((int) $user->PersonID === (int) $person->PersonID) {
            return true;
        }

        return $user->role()
            ->whereIn('RoleName', ['SuperAdmin', 'AdminQetaa'])
            ->exists();
    }
}
