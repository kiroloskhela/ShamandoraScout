<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QetaaTreeController extends Controller
{
    /**
     * Show the Qetaa tree page.
     * The logged-in user only sees people they are responsible for (same Qetaa via GroupQetaa).
     */
    public function index(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();

        // ── 1. Which Qetaas does this person serve? ─────────────────────────
        $servedQetaaIds = DB::table('GroupQetaa as gq')
            ->join('PersonGroup as pg', 'pg.GroupID', '=', 'gq.GroupID')
            ->where('pg.PersonID', $userId)
            ->pluck('gq.QetaaID')
            ->unique()
            ->values();

        // ── 2. Load all Qetaas (static master list) ──────────────────────────
        $allQetaas = DB::table('Qetaa')->orderBy('QetaaID')->get();

        // ── 3. Load all groups for those Qetaas (types 2 & 3 only) ──────────
        //    GroupTable: GroupID, GroupTypeID, IncludedUnderGroupID, GroupName
        //    GroupQetaa: ID, GroupID, QetaaID
        $groups = DB::table('GroupTable as gt')
            ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
            ->whereIn('gq.QetaaID', $servedQetaaIds)
            ->whereIn('gt.GroupTypeID', [2, 3])
            ->select(
                'gt.GroupID',
                'gt.GroupTypeID',
                'gt.IncludedUnderGroupID',
                'gt.GroupName',
                'gq.QetaaID'
            )
            ->get();

        // ── 4. People the logged-in user serves ──────────────────────────────
        $people = DB::select("
            SELECT DISTINCT
                pi.PersonID,
                pi.FirstName,
                pi.SecondName,
                pi.ShamandoraCode,
                ri.RotbaID,
                ri.RotbaName,
                pq.QetaaID,
                pg2.GroupID
            FROM PersonInformation pi
            LEFT JOIN PersonQetaa pq        ON pi.PersonID  = pq.PersonID
            LEFT JOIN Qetaa q               ON pq.QetaaID   = q.QetaaID
            LEFT JOIN PersonGroup pg2       ON pg2.PersonID = pi.PersonID
            LEFT JOIN PersonRotba pr        ON pr.PersonID  = pi.PersonID
            LEFT JOIN RotbaInformation ri   ON ri.RotbaID   = pr.RotbaID
            JOIN  GroupQetaa gq             ON gq.QetaaID   = q.QetaaID
            JOIN  PersonGroup pg3           ON pg3.GroupID  = gq.GroupID
            WHERE q.QetaaID IN (
                SELECT gq2.QetaaID
                FROM GroupQetaa gq2
                WHERE gq2.GroupID IN (
                    SELECT pg4.GroupID
                    FROM PersonGroup pg4
                    WHERE pg4.PersonID = ?
                )
            )
            ORDER BY pi.ShamandoraCode ASC
        ", [$userId]);

        // ── 5. Load seasons ──────────────────────────────────────────────────
        $seasons = DB::table('Season')->orderByDesc('SeasonYear')->get();
        $currentSeasonId = $request->query('season') ?? ($seasons->first()->SeasonID ?? null);

        // ── 6. Build tree structure ──────────────────────────────────────────
        //    For each Qetaa → top-level groups (those whose IncludedUnderGroupID
        //    is 0 or not present in groups list) → children groups under them.

        $groupsByQetaa = $groups->groupBy('QetaaID');
        $peopleByGroup = collect($people)->groupBy('GroupID');

        $tree = $allQetaas->map(function ($qetaa) use ($groupsByQetaa, $peopleByGroup, $servedQetaaIds) {
            $qGroups   = $groupsByQetaa->get($qetaa->QetaaID, collect());
            $groupIds  = $qGroups->pluck('GroupID')->toArray();

            // Top-level = IncludedUnderGroupID not in the group list for this qetaa
            $topLevel = $qGroups->filter(fn($g) => !in_array($g->IncludedUnderGroupID, $groupIds));
            $children = $qGroups->filter(fn($g) =>  in_array($g->IncludedUnderGroupID, $groupIds));

            $topLevel = $topLevel->map(function ($g) use ($children, $peopleByGroup) {
                $g->children = $children
                    ->where('IncludedUnderGroupID', $g->GroupID)
                    ->map(function ($child) use ($peopleByGroup) {
                        $child->people = $peopleByGroup->get($child->GroupID, collect());
                        return $child;
                    })->values();
                $g->people = $peopleByGroup->get($g->GroupID, collect());
                return $g;
            })->values();

            return [
                'qetaa'       => $qetaa,
                'groups'      => $topLevel,
                'is_served'   => $servedQetaaIds->contains($qetaa->QetaaID),
                'total_people'=> $peopleByGroup
                    ->only($qGroups->pluck('GroupID')->toArray())
                    ->flatten(1)->count(),
            ];
        });

        return view('tree.index', compact('tree', 'seasons', 'currentSeasonId', 'userId'));
    }

    /**
     * Store a new group (طليعة GroupTypeID=2 or فريق GroupTypeID=3).
     */
    public function storeGroup(Request $request)
    {
        $request->validate([
            'GroupName'            => 'required|string|max:50',
            'GroupTypeID'          => 'required|in:2,3',
            'QetaaID'              => 'required|integer',
            'IncludedUnderGroupID' => 'required|integer', // 0 if top-level
            'SeasonID'             => 'required|integer',
        ]);

        // Enforce: TypeID=3 (فريق) cannot be under TypeID=2 (طليعة)
        // TypeID=2 (طليعة) can be under TypeID=3 (فريق) or top-level
        if ($request->IncludedUnderGroupID > 0) {
            $parent = DB::table('GroupTable')
                ->where('GroupID', $request->IncludedUnderGroupID)
                ->first();
            if ($parent && $parent->GroupTypeID == 2 && $request->GroupTypeID == 3) {
                return response()->json([
                    'error' => 'فريق (type 3) cannot be placed under طليعة (type 2).'
                ], 422);
            }
        }

        $lastGroupID = DB::table('GroupTable')->orderBy('GroupID', 'desc')->first();
        $newId = $lastGroupID ? $lastGroupID->GroupID + 1 : 1;

        DB::table('GroupTable')->insert([
            'GroupID'              => $newId,
            'GroupTypeID'          => $request->GroupTypeID,
            'IncludedUnderGroupID' => $request->IncludedUnderGroupID ?? 0,
            'GroupName'            => $request->GroupName,
        ]);

        // Link to Qetaa
        DB::table('GroupQetaa')->insert([
            'GroupID' => $newId,
            'QetaaID' => $request->QetaaID,
        ]);

        return response()->json(['success' => true, 'GroupID' => $newId]);
    }

    /**
     * Delete a group (and cascade removes from GroupQetaa).
     */
    public function deleteGroup(Request $request, $groupId)
    {
        DB::table('GroupQetaa')->where('GroupID', $groupId)->delete();
        DB::table('PersonGroup')->where('GroupID', $groupId)->delete();
        DB::table('GroupTable')->where('GroupID', $groupId)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Add a person to a group.
     */
    public function storePerson(Request $request)
    {
        $request->validate([
            'PersonID' => 'required|integer',
            'GroupID'  => 'required|integer',
        ]);

        $exists = DB::table('PersonGroup')
            ->where('PersonID', $request->PersonID)
            ->where('GroupID',  $request->GroupID)
            ->exists();

        if (!$exists) {
            DB::table('PersonGroup')->insert([
                'PersonID' => $request->PersonID,
                'GroupID'  => $request->GroupID,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove a person from a group.
     */
    public function removePerson(Request $request)
    {
        DB::table('PersonGroup')
            ->where('PersonID', $request->PersonID)
            ->where('GroupID',  $request->GroupID)
            ->delete();

        return response()->json(['success' => true]);
    }
}