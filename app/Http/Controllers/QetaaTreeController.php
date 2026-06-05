<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QetaaTreeController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();

        $servedQetaaIds = DB::table('GroupQetaa as gq')
            ->join('PersonGroup as pg', 'pg.GroupID', '=', 'gq.GroupID')
            ->where('pg.PersonID', $userId)
            ->pluck('gq.QetaaID')
            ->unique()
            ->values();

        $allQetaas = DB::table('Qetaa')->orderBy('QetaaID')->get();

        $groups = DB::table('GroupTable as gt')
            ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
            ->whereIn('gq.QetaaID', $servedQetaaIds)
            ->whereIn('gt.GroupTypeID', [2, 3])
            ->select('gt.GroupID', 'gt.GroupTypeID', 'gt.IncludedUnderGroupID', 'gt.GroupName', 'gq.QetaaID')
            ->get();

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
                SELECT gq2.QetaaID FROM GroupQetaa gq2
                WHERE gq2.GroupID IN (
                    SELECT pg4.GroupID FROM PersonGroup pg4 WHERE pg4.PersonID = ?
                )
            )
            ORDER BY pi.ShamandoraCode ASC
        ", [$userId]);

        $seasons = DB::table('Season')->orderByDesc('SeasonYear')->get();
        $currentSeasonId = $request->query('season') ?? ($seasons->first()->SeasonID ?? null);

        $groupsByQetaa = $groups->groupBy('QetaaID');
        $peopleByGroup = collect($people)->groupBy('GroupID');

        $tree = $allQetaas->map(function ($qetaa) use ($groupsByQetaa, $peopleByGroup, $servedQetaaIds) {
            $qGroups  = $groupsByQetaa->get($qetaa->QetaaID, collect());
            $groupIds = $qGroups->pluck('GroupID')->toArray();

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
                'qetaa'        => $qetaa,
                'groups'       => $topLevel,
                'is_served'    => $servedQetaaIds->contains($qetaa->QetaaID),
                'total_people' => $peopleByGroup->only($qGroups->pluck('GroupID')->toArray())->flatten(1)->count(),
            ];
        });

        return view('tree.index', compact('tree', 'seasons', 'currentSeasonId', 'userId'));
    }

    /**
     * Search persons by name (for the person-add modal autocomplete).
     */
    public function searchPersons(Request $request)
    {
        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = DB::table('PersonInformation as pi')
            ->leftJoin('PersonRotba as pr', 'pr.PersonID', '=', 'pi.PersonID')
            ->leftJoin('RotbaInformation as ri', 'ri.RotbaID', '=', 'pr.RotbaID')
            ->where(function ($query) use ($q) {
                $query->where(DB::raw("CONCAT(pi.FirstName, ' ', pi.SecondName)"), 'LIKE', "%{$q}%")
                      ->orWhere('pi.FirstName',      'LIKE', "%{$q}%")
                      ->orWhere('pi.SecondName',     'LIKE', "%{$q}%")
                      ->orWhere('pi.ShamandoraCode', 'LIKE', "%{$q}%");
            })
            ->select('pi.PersonID', 'pi.FirstName', 'pi.SecondName', 'pi.ShamandoraCode', 'ri.RotbaName')
            ->orderBy('pi.ShamandoraCode')
            ->limit(20)
            ->get();

        return response()->json($results);
    }

    /**
     * Return all ranks from RotbaInformation.
     */
    public function getRotbaList()
    {
        $rotbas = DB::table('RotbaInformation')->orderBy('RotbaID')->get();
        return response()->json($rotbas);
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'GroupName'            => 'required|string|max:50',
            'GroupTypeID'          => 'required|in:2,3',
            'QetaaID'              => 'required|integer',
            'IncludedUnderGroupID' => 'required|integer',
            'SeasonID'             => 'required|integer',
        ]);

        if ($request->IncludedUnderGroupID > 0) {
            $parent = DB::table('GroupTable')->where('GroupID', $request->IncludedUnderGroupID)->first();
            if ($parent && $parent->GroupTypeID == 2 && $request->GroupTypeID == 3) {
                return response()->json(['error' => 'لا يمكن وضع فريق تحت طليعة.'], 422);
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

        DB::table('GroupQetaa')->insert(['GroupID' => $newId, 'QetaaID' => $request->QetaaID]);

        return response()->json(['success' => true, 'GroupID' => $newId]);
    }

    public function deleteGroup(Request $request, $groupId)
    {
        DB::table('GroupQetaa')->where('GroupID', $groupId)->delete();
        DB::table('PersonGroup')->where('GroupID', $groupId)->delete();
        DB::table('GroupTable')->where('GroupID', $groupId)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Add a person to a group and optionally assign/update their rotba.
     */
    public function storePerson(Request $request)
    {
        $request->validate([
            'PersonID' => 'required|integer',
            'GroupID'  => 'required|integer',
            'RotbaID'  => 'nullable|integer',
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

        // Assign or update rotba if provided
        if ($request->filled('RotbaID')) {
            $rotbaExists = DB::table('PersonRotba')
                ->where('PersonID', $request->PersonID)
                ->exists();

            if ($rotbaExists) {
                DB::table('PersonRotba')
                    ->where('PersonID', $request->PersonID)
                    ->update(['RotbaID' => $request->RotbaID]);
            } else {
                DB::table('PersonRotba')->insert([
                    'PersonID' => $request->PersonID,
                    'RotbaID'  => $request->RotbaID,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function removePerson(Request $request)
    {
        DB::table('PersonGroup')
            ->where('PersonID', $request->PersonID)
            ->where('GroupID',  $request->GroupID)
            ->delete();

        return response()->json(['success' => true]);
    }
}