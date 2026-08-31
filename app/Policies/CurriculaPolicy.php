<?php

namespace App\Policies;

use App\Domain\Authz\PermissionService;
use App\Models\User;

class CurriculaPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->staff($user);
    }

    public function view(User $user): bool
    {
        return $this->staff($user);
    }

    public function create(User $user): bool
    {
        return $this->staff($user);
    }

    public function update(User $user): bool
    {
        return $this->staff($user);
    }

    public function delete(User $user): bool
    {
        return $this->staff($user);
    }

    private function staff(User $user): bool
    {
        return app(PermissionService::class)->isStaff($user);
    }
}
