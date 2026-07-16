<?php

namespace App\Domain\OrgTree;

use Illuminate\Support\Facades\DB;

/**
 * Load GroupTable once and walk the adjacency list in memory.
 */
class GroupTreeService
{
    /** @var array<int, object>|null */
    private ?array $byId = null;

    /** @var array<int, array<int, object>>|null */
    private ?array $children = null;

    public function warm(): void
    {
        if ($this->byId !== null) {
            return;
        }

        $rows = DB::table('GroupTable')
            ->leftJoin('GroupType', 'GroupTable.GroupTypeID', '=', 'GroupType.GroupTypeID')
            ->select(
                'GroupTable.GroupID',
                'GroupTable.IncludedUnderGroupID',
                'GroupTable.GroupName',
                'GroupType.GroupTypeName'
            )
            ->get();

        $this->byId = [];
        $this->children = [];
        foreach ($rows as $row) {
            $id = (int) $row->GroupID;
            $this->byId[$id] = $row;
            $parent = (int) $row->IncludedUnderGroupID;
            $this->children[$parent][] = $row;
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
        return (int) DB::transaction(function () use ($name, $typeId, $parentGroupId, $qetaaId) {
            $newId = DB::table('GroupTable')->insertGetId([
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
}
