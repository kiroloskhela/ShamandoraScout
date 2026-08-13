<?php

namespace App\Policies;

use App\Domain\Authz\PermissionService;
use App\Models\User;

/**
 * Event booking finance mutations: web.finance.manage; delete uses web.finance.delete_booking.
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
        return app(PermissionService::class)->userCan($user, 'web.finance.delete_booking');
    }

    private function hasFinanceRole(User $user): bool
    {
        return app(PermissionService::class)->userCan($user, 'web.finance.manage');
    }
}
