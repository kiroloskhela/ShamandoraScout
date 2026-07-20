<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Team structure mutations: limited to qetaat the user serves via group membership.
 */
class TreePolicy
{
    public function manageQetaa(User $user, int $qetaaId): bool
    {
        return $this->servedQetaaIds((int) $user->PersonID)->contains($qetaaId);
    }

    public function manageGroup(User $user, int $groupId): bool
    {
        $qetaaId = DB::table('GroupQetaa')
            ->where('GroupID', $groupId)
            ->value('QetaaID');

        if ($qetaaId === null) {
            return false;
        }

        return $this->manageQetaa($user, (int) $qetaaId);
    }

    /**
     * @return Collection<int, int>
     */
    public function servedQetaaIds(int $userId): Collection
    {
        return DB::table('GroupQetaa as gq')
            ->join('PersonGroup as pg', 'pg.GroupID', '=', 'gq.GroupID')
            ->where('pg.PersonID', $userId)
            ->pluck('gq.QetaaID')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }
}
