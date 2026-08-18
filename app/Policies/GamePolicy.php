<?php

namespace App\Policies;

use App\Domain\Authz\PermissionService;
use App\Models\Game;
use App\Models\User;

/**
 * Games: any authenticated user may view/create/edit; delete is SuperAdmin-only.
 */
class GamePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Game $game): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Game $game): bool
    {
        return true;
    }

    public function delete(User $user, Game $game): bool
    {
        return app(PermissionService::class)->isSuperAdmin($user);
    }
}
