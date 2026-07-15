<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

/**
 * Games API authorization.
 * Phase 2: bound to Eloquent Game; any authenticated Sanctum user may manage games
 * (same product rule as phase 1 Gates).
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
        return true;
    }
}
