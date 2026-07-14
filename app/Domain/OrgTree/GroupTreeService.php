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

        while ($current && !isset($seen[$current])) {
            $seen[$current] = true;
            $row = $this->byId[$current] ?? null;
            if (!$row) {
                break;
            }
            $parts[] = trim(($row->GroupTypeName ?? '') . ' ' . ($row->GroupName ?? ''));
            $parent = (int) $row->IncludedUnderGroupID;
            if ($parent === 0) {
                break;
            }
            $current = $parent;
        }

        return implode(' -> ', $parts);
    }
}
