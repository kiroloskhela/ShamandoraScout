<?php

namespace App\Policies;

use App\Domain\Authz\PermissionService;
use App\Models\Game;
use App\Models\User;

/**
 * Games: staff may view/create/edit; delete is SuperAdmin-only.
 */
class GamePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->staff($user);
    }

    public function view(User $user, Game $game): bool
    {
        return $this->staff($user);
    }

    public function create(User $user): bool
    {
        return $this->staff($user);
    }

    public function update(User $user, Game $game): bool
    {
        return $this->staff($user);
    }

    public function delete(User $user, Game $game): bool
    {
        return app(PermissionService::class)->isSuperAdmin($user);
    }

    private function staff(User $user): bool
    {
        return app(PermissionService::class)->isStaff($user);
    }
}
