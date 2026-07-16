<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Person record access: own PersonID, SuperAdmin (any), or AdminQetaa (shared Qetaa only).
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

        $roleNames = $user->role()->pluck('RoleName');

        if ($roleNames->contains('SuperAdmin')) {
            return true;
        }

        if (! $roleNames->contains('AdminQetaa')) {
            return false;
        }

        $adminQetaas = DB::table('PersonQetaa')
            ->where('PersonID', $user->PersonID)
            ->pluck('QetaaID');

        if ($adminQetaas->isEmpty()) {
            return false;
        }

        return DB::table('PersonQetaa')
            ->where('PersonID', $person->PersonID)
            ->whereIn('QetaaID', $adminQetaas)
            ->exists();
    }
}
