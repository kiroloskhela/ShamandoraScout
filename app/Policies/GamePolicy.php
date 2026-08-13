<?php

namespace App\Policies;

use App\Domain\Authz\PermissionService;
use App\Models\Game;
use App\Models\User;

/**
 * Games: any authenticated user may view; mutate requires web.games.manage or api.games.mutate.
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
        $p = app(PermissionService::class);

        return $p->userCan($user, 'web.games.manage') || $p->userCan($user, 'api.games.mutate');
    }

    public function update(User $user, Game $game): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Game $game): bool
    {
        return $this->create($user);
    }
}
