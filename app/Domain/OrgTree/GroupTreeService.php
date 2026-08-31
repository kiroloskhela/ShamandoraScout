<?php

namespace App\Domain\OrgTree;

use App\Support\ManualPrimaryKey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Load GroupTable once and walk the adjacency list in memory.
 */
class GroupTreeService
{
    public const CACHE_KEY = 'tree.groups';

    public const TTL_SECONDS = 1800;

    /** @var array<int, object>|null */
    private ?array $byId = null;

    /** @var array<int, array<int, object>>|null */
    private ?array $children = null;

    public function warm(): void
    {
        if ($this->byId !== null) {
            return;
        }

        $this->byId = [];
        $this->children = [];
        foreach ($this->cachedRows() as $row) {
            $id = (int) $row->GroupID;
            $this->byId[$id] = $row;
            $parent = (int) $row->IncludedUnderGroupID;
            $this->children[$parent][] = $row;
        }
    }

    public function bustCache(): void
    {
        $this->byId = null;
        $this->children = null;

        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable) {
            // Next warm() falls back to MySQL.
        }
    }

    public function nodesBelow(int $groupId): array
    {
        $this->warm();
        $ids = [];
        $stack = [$groupId];
        $seen = [];

        while ($stack) {
            $current = array_pop($stack);
            if (isset($seen[$current])) {
                continue; // cycle guard
            }
            $seen[$current] = true;
            foreach ($this->children[$current] ?? [] as $child) {
                $cid = (int) $child->GroupID;
                $ids[] = $cid;
                $stack[] = $cid;
            }
        }

        return $ids;
    }

    public function parentsPathString(int $groupId): string
    {
        $this->warm();
        $parts = [];
        $current = $groupId;
        $seen = [];

        while ($current && ! isset($seen[$current])) {
            $seen[$current] = true;
            $row = $this->byId[$current] ?? null;
            if (! $row) {
                break;
            }
            $parts[] = trim(($row->GroupTypeName ?? '').' '.($row->GroupName ?? ''));
            $parent = (int) $row->IncludedUnderGroupID;
            if ($parent === 0) {
                break;
            }
            $current = $parent;
        }

        return implode(' -> ', $parts);
    }

    public function createGroup(string $name, int $typeId, int $parentGroupId, int $qetaaId): int
    {
        $newId = (int) DB::transaction(function () use ($name, $typeId, $parentGroupId, $qetaaId) {
            // GroupTable.GroupID is not AUTO_INCREMENT in production.
            $newId = ManualPrimaryKey::next('GroupTable', 'GroupID');

            DB::table('GroupTable')->insert([
                'GroupID' => $newId,
                'GroupTypeID' => $typeId,
                'IncludedUnderGroupID' => $parentGroupId,
                'GroupName' => $name,
            ]);

            DB::table('GroupQetaa')->insert([
                'GroupID' => $newId,
                'QetaaID' => $qetaaId,
            ]);

            return $newId;
        });

        $this->bustCache();

        return $newId;
    }

    public function renameGroup(int $groupId, string $name): void
    {
        DB::table('GroupTable')->where('GroupID', $groupId)->update([
            'GroupName' => $name,
        ]);
        $this->bustCache();
    }

    public function deleteGroups(iterable $groupIds): void
    {
        $ids = collect($groupIds)->map(fn ($id) => (int) $id)->values();

        DB::transaction(function () use ($ids) {
            DB::table('PersonGroup')->whereIn('GroupID', $ids)->delete();
            DB::table('GroupQetaa')->whereIn('GroupID', $ids)->delete();
            $ids->reverse()->each(function ($groupId) {
                DB::table('GroupTable')->where('GroupID', $groupId)->delete();
            });
        });

        $this->bustCache();
    }

    /**
     * @param  iterable<int>  $personIds
     * @param  array<int, int|string|null>  $rotbaByPerson
     */
    public function assignPeopleToGroup(int $groupId, iterable $personIds, array $rotbaByPerson = [], int|string|null $defaultRotbaId = null): int
    {
        $ids = collect($personIds)->map(fn ($id) => (int) $id)->unique()->values();

        DB::transaction(function () use ($groupId, $ids, $rotbaByPerson, $defaultRotbaId) {
            DB::table('PersonGroup')->whereIn('PersonID', $ids)->delete();

            DB::table('PersonGroup')->insert(
                $ids->map(fn ($personId) => [
                    'PersonID' => $personId,
                    'GroupID' => $groupId,
                ])->all()
            );

            foreach ($ids as $personId) {
                $rotbaId = array_key_exists($personId, $rotbaByPerson)
                    ? $rotbaByPerson[$personId]
                    : $defaultRotbaId;

                if ($rotbaId !== null && $rotbaId !== '') {
                    DB::table('PersonRotba')->updateOrInsert(
                        ['PersonID' => $personId],
                        ['RotbaID' => $rotbaId]
                    );
                }
            }
        });

        return $ids->count();
    }

    public function updatePersonRotba(int $personId, int|string|null $rotbaId): void
    {
        if ($rotbaId !== null && $rotbaId !== '') {
            DB::table('PersonRotba')->updateOrInsert(
                ['PersonID' => $personId],
                ['RotbaID' => $rotbaId]
            );

            return;
        }

        DB::table('PersonRotba')->where('PersonID', $personId)->delete();
    }

    public function removePersonFromGroup(int $personId, int $groupId): void
    {
        DB::table('PersonGroup')
            ->where('PersonID', $personId)
            ->where('GroupID', $groupId)
            ->delete();
    }

    /**
     * @return Collection<int, object>
     */
    private function cachedRows(): Collection
    {
        try {
            $rows = Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, function () {
                return $this->queryRows()->all();
            });

            return collect($rows)->map(fn ($row) => is_object($row) ? clone $row : $row);
        } catch (Throwable) {
            return $this->queryRows();
        }
    }

    /**
     * @return Collection<int, object>
     */
    private function queryRows(): Collection
    {
        return DB::table('GroupTable')
            ->leftJoin('GroupType', 'GroupTable.GroupTypeID', '=', 'GroupType.GroupTypeID')
            ->select(
                'GroupTable.GroupID',
                'GroupTable.IncludedUnderGroupID',
                'GroupTable.GroupName',
                'GroupType.GroupTypeName'
            )
            ->get();
    }
}
