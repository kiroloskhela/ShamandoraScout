<?php

namespace App\Policies;

use App\Models\User;

/**
 * Curricula: any authenticated user may view/download and publish (create/update/delete).
 */
class CurriculaPolicy
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
