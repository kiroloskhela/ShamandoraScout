<?php

namespace App\Policies;

use App\Models\User;

/**
 * Games API authorization (phase 1).
 * Matches current Games API: any authenticated Sanctum user may manage games.
 * Prefer Gate abilities (games.*) until a Game Eloquent model exists.
 */
class GamePolicy
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
        return true;
    }

    public function update(User $user): bool
    {
        return true;
    }

    public function delete(User $user): bool
    {
        return true;
    }
}
