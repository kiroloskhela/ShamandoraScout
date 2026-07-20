<?php

namespace App\Policies;

use App\Models\User;

/**
 * Event booking finance mutations: Finance roles; delete restricted to SuperAdmin.
 */
class EventBookingPolicy
{
    public function create(User $user): bool
    {
        return $this->hasFinanceRole($user);
    }

    public function update(User $user): bool
    {
        return $this->hasFinanceRole($user);
    }

    public function delete(User $user): bool
    {
        return $user->role()->where('RoleName', 'SuperAdmin')->exists();
    }

    private function hasFinanceRole(User $user): bool
    {
        return $user->role()->whereIn('RoleName', ['SuperAdmin', 'Finance', 'AdminFinance'])->exists();
    }
}
