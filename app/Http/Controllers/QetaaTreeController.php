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
        $servedQetaaIds = $this->servedQetaaIds($userId);
        [$seasons, $currentSeasonId] = $this->seasonContext($request);
        $servedQetaas = DB::table('Qetaa')
            ->whereIn('QetaaID', $servedQetaaIds)
            ->orderBy('QetaaID')
            ->get();

        $selectedQetaaId = $request->query('qetaa');
        if ($selectedQetaaId && !$servedQetaaIds->contains((int) $selectedQetaaId)) {
            $selectedQetaaId = null;
        }

        $visibleQetaaIds = $selectedQetaaId ? collect([(int) $selectedQetaaId]) : $servedQetaaIds;
        $tree = $this->buildTree($visibleQetaaIds, $servedQetaaIds);
        $pageTitle = 'هيكل الفريق';

        return view('tree.index', compact('tree', 'seasons', 'currentSeasonId', 'userId', 'pageTitle', 'servedQetaas', 'selectedQetaaId'));
    }

    public function auxiliary(Request $request)
    {
        $userId = Auth::id();
        $servedQetaaIds = $this->servedQetaaIds($userId);
        $servedQetaas = DB::table('Qetaa')
            ->whereIn('QetaaID', $servedQetaaIds)
            ->orderBy('QetaaID')
            ->get();

        $selectedQetaaId = $request->query('qetaa');
        if (!$selectedQetaaId && $servedQetaas->count() === 1) {
            $selectedQetaaId = $servedQetaas->first()->QetaaID;
        }

        if ($selectedQetaaId && !$servedQetaaIds->contains((int) $selectedQetaaId)) {
            $selectedQetaaId = null;
        }

        $teams = collect();
        $selectedTeamId = $request->query('team');
        $selectedTeam = null;
        $talaea = collect();
        $directTalaeaCount = 0;

        if ($selectedQetaaId) {
            $teams = DB::table('GroupTable as gt')
                ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
                ->where('gq.QetaaID', $selectedQetaaId)
                ->where('gt.GroupTypeID', 2)
                ->select('gt.GroupID', 'gt.GroupName')
                ->orderBy('gt.GroupName')
                ->get();

            $directTalaeaCount = DB::table('GroupTable as gt')
                ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
                ->where('gq.QetaaID', $selectedQetaaId)
                ->where('gt.GroupTypeID', 3)
                ->where(function ($query) {
                    $query->whereNull('gt.IncludedUnderGroupID')
                        ->orWhere('gt.IncludedUnderGroupID', 0);
                })
                ->count();

            if (!$selectedTeamId && $directTalaeaCount > 0 && $teams->isEmpty()) {
                $selectedTeamId = 'direct';
            } elseif (!$selectedTeamId && $directTalaeaCount === 0 && $teams->count() === 1) {
                $selectedTeamId = $teams->first()->GroupID;
            }

            if ($selectedTeamId && $selectedTeamId !== 'direct' && !$teams->pluck('GroupID')->contains((int) $selectedTeamId)) {
                $selectedTeamId = null;
            }

            if ($selectedTeamId === 'direct') {
                $selectedTeam = (object) [
                    'GroupID' => 'direct',
                    'GroupName' => 'الطلائع المباشرة',
                ];

                $talaea = DB::table('GroupTable as gt')
                    ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
                    ->where('gq.QetaaID', $selectedQetaaId)
                    ->where('gt.GroupTypeID', 3)
                    ->where(function ($query) {
                        $query->whereNull('gt.IncludedUnderGroupID')
                            ->orWhere('gt.IncludedUnderGroupID', 0);
                    })
                    ->select('gt.GroupID', 'gt.GroupName')
                    ->orderBy('gt.GroupName')
                    ->get();
            } elseif ($selectedTeamId) {
                $selectedTeam = $teams->firstWhere('GroupID', (int) $selectedTeamId);

                $talaea = DB::table('GroupTable as gt')
                    ->where('gt.GroupTypeID', 3)
                    ->where('gt.IncludedUnderGroupID', $selectedTeamId)
                    ->select('gt.GroupID', 'gt.GroupName')
                    ->orderBy('gt.GroupName')
                    ->get();
            }

            if ($selectedTeamId) {
                $taleiaIds = $talaea->pluck('GroupID');
                $peopleByTaleia = $taleiaIds->isEmpty()
                    ? collect()
                    : DB::table('PersonGroup as pg')
                        ->join('PersonInformation as pi', 'pi.PersonID', '=', 'pg.PersonID')
                        ->leftJoin('PersonRotba as pr', 'pr.PersonID', '=', 'pi.PersonID')
                        ->leftJoin('RotbaInformation as ri', 'ri.RotbaID', '=', 'pr.RotbaID')
                        ->leftJoin('PersonImages as pim', 'pim.PersonID', '=', 'pi.PersonID')
                        ->whereIn('pg.GroupID', $taleiaIds)
                        ->select(
                            'pg.GroupID',
                            'pi.PersonID',
                            'pi.FirstName',
                            'pi.SecondName',
                            'pi.ShamandoraCode',
                            'ri.RotbaID',
                            'ri.RotbaName',
                            'pim.PersonSystemImagePath'
                        )
                        ->distinct()
                        ->orderByRaw('CASE WHEN ri.RotbaID = 12 THEN 1 ELSE 0 END')
                        ->orderByRaw('CASE WHEN ri.RotbaID IS NULL THEN 1 ELSE 0 END')
                        ->orderBy('ri.RotbaID')
                        ->orderBy('pi.ShamandoraCode')
                        ->get()
                        ->groupBy('GroupID');

                $talaea = $talaea->map(function ($taleia) use ($peopleByTaleia) {
                    $taleia->people = $peopleByTaleia->get($taleia->GroupID, collect());
                    return $taleia;
                });
            }
        }

        return view('tree.auxiliary', compact(
            'servedQetaas',
            'selectedQetaaId',
            'teams',
            'selectedTeamId',
            'selectedTeam',
            'talaea',
            'directTalaeaCount'
        ));
    }

    private function servedQetaaIds($userId)
    {
        return DB::table('GroupQetaa as gq')
            ->join('PersonGroup as pg', 'pg.GroupID', '=', 'gq.GroupID')
            ->where('pg.PersonID', $userId)
            ->pluck('gq.QetaaID')
            ->unique()
            ->values();
    }

    private function seasonContext(Request $request)
    {
        $seasons = DB::table('Season')->orderByDesc('SeasonYear')->get();
        $currentSeasonId = $request->query('season') ?? ($seasons->first()->SeasonID ?? null);

        return [$seasons, $currentSeasonId];
    }

    private function qetaaIdForGroup($groupId)
    {
        return DB::table('GroupQetaa')
            ->where('GroupID', $groupId)
            ->value('QetaaID');
    }

    private function buildTree($visibleQetaaIds = null, $servedQetaaIds = null)
    {
        $servedQetaaIds = $servedQetaaIds ?? collect();
        $allQetaas = DB::table('Qetaa')->orderBy('QetaaID');

        if ($visibleQetaaIds !== null) {
            $allQetaas->whereIn('QetaaID', $visibleQetaaIds);
        }

        $allQetaas = $allQetaas->get();

        $groups = DB::table('GroupTable as gt')
            ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
            ->whereIn('gt.GroupTypeID', [2, 3])
            ->select('gt.GroupID', 'gt.GroupTypeID', 'gt.IncludedUnderGroupID', 'gt.GroupName', 'gq.QetaaID');

        if ($visibleQetaaIds !== null) {
            $groups->whereIn('gq.QetaaID', $visibleQetaaIds);
        }

        $groups = $groups->get();

        $groupIds = $groups->pluck('GroupID')->unique()->values();

        $people = $groupIds->isEmpty()
            ? collect()
            : DB::table('PersonGroup as pg')
                ->join('PersonInformation as pi', 'pi.PersonID', '=', 'pg.PersonID')
                ->leftJoin('PersonRotba as pr', 'pr.PersonID', '=', 'pi.PersonID')
                ->leftJoin('RotbaInformation as ri', 'ri.RotbaID', '=', 'pr.RotbaID')
                ->whereIn('pg.GroupID', $groupIds)
                ->select(
                    'pi.PersonID',
                    'pi.FirstName',
                    'pi.SecondName',
                    'pi.ShamandoraCode',
                    'ri.RotbaID',
                    'ri.RotbaName',
                    'pg.GroupID'
                )
                ->distinct()
                ->orderBy('pi.ShamandoraCode')
                ->get();

        $groupsByQetaa = $groups->groupBy('QetaaID');
        $peopleByGroup = $people->groupBy('GroupID');

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
                'total_groups'  => $qGroups->count(),
                'total_people' => $peopleByGroup->only($qGroups->pluck('GroupID')->toArray())->flatten(1)->count(),
            ];
        });

        return $tree;
    }

    /**
     * Search persons by name (for the person-add modal autocomplete).
     */
    public function searchPersons(Request $request)
    {
        $q = trim($request->query('q', ''));
        $groupId = (int) $request->query('group_id', 0);

        if (strlen($q) < 2 || !$groupId) {
            return response()->json([]);
        }

        $qetaaId = $this->qetaaIdForGroup($groupId);
        if (!$qetaaId || !$this->servedQetaaIds(Auth::id())->contains((int) $qetaaId)) {
            return response()->json([]);
        }

        $results = DB::table('PersonInformation as pi')
            ->join('PersonQetaa as pq', 'pq.PersonID', '=', 'pi.PersonID')
            ->leftJoin('PersonRotba as pr', 'pr.PersonID', '=', 'pi.PersonID')
            ->leftJoin('RotbaInformation as ri', 'ri.RotbaID', '=', 'pr.RotbaID')
            ->where('pq.QetaaID', $qetaaId)
            ->where(function ($query) use ($q) {
                $query->where(DB::raw("CONCAT(pi.FirstName, ' ', pi.SecondName)"), 'LIKE', "%{$q}%")
                      ->orWhere('pi.FirstName',      'LIKE', "%{$q}%")
                      ->orWhere('pi.SecondName',     'LIKE', "%{$q}%")
                      ->orWhere('pi.ShamandoraCode', 'LIKE', "%{$q}%");
            })
            ->select('pi.PersonID', 'pi.FirstName', 'pi.SecondName', 'pi.ShamandoraCode', 'ri.RotbaName')
            ->distinct()
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
        $rotbas = DB::table('RotbaInformation')
            ->whereIn('RotbaID', [1, 2, 11, 12])
            ->orderBy('RotbaID')
            ->get();
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

        if (!$this->servedQetaaIds(Auth::id())->contains((int) $request->QetaaID)) {
            return response()->json(['error' => 'لا يمكنك تعديل هذا القطاع.'], 403);
        }

        if ($request->IncludedUnderGroupID > 0) {
            $parent = DB::table('GroupTable')->where('GroupID', $request->IncludedUnderGroupID)->first();

            if (!$parent) {
                return response()->json(['error' => 'المجموعة الرئيسية غير موجودة.'], 422);
            }

            $parentInQetaa = DB::table('GroupQetaa')
                ->where('GroupID', $request->IncludedUnderGroupID)
                ->where('QetaaID', $request->QetaaID)
                ->exists();

            if (!$parentInQetaa) {
                return response()->json(['error' => 'المجموعة الرئيسية لا تتبع هذا القطاع.'], 422);
            }

            if ((int) $parent->GroupTypeID !== 2 || (int) $request->GroupTypeID !== 3) {
                return response()->json(['error' => 'يمكن إضافة طليعة فقط داخل فريق.'], 422);
            }
        } elseif (!in_array((int) $request->GroupTypeID, [2, 3], true)) {
            return response()->json(['error' => 'نوع المجموعة غير صحيح.'], 422);
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
        $canAccessGroup = DB::table('GroupQetaa')
            ->where('GroupID', $groupId)
            ->whereIn('QetaaID', $this->servedQetaaIds(Auth::id()))
            ->exists();

        if (!$canAccessGroup) {
            return response()->json(['error' => 'لا يمكنك حذف هذه المجموعة.'], 403);
        }

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

        $group = DB::table('GroupTable')->where('GroupID', $request->GroupID)->first();
        if (!$group || (int) $group->GroupTypeID !== 3) {
            return response()->json(['error' => 'يمكن إضافة الأشخاص داخل طليعة فقط.'], 422);
        }

        $canAccessGroup = DB::table('GroupQetaa')
            ->where('GroupID', $request->GroupID)
            ->whereIn('QetaaID', $this->servedQetaaIds(Auth::id()))
            ->exists();

        if (!$canAccessGroup) {
            return response()->json(['error' => 'لا يمكنك تعديل هذه المجموعة.'], 403);
        }

        $qetaaId = $this->qetaaIdForGroup($request->GroupID);
        $personInQetaa = DB::table('PersonQetaa')
            ->where('PersonID', $request->PersonID)
            ->where('QetaaID', $qetaaId)
            ->exists();

        if (!$personInQetaa) {
            return response()->json(['error' => 'هذا الشخص غير تابع للقطاع الخاص بهذه الطليعة.'], 422);
        }

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
        $group = DB::table('GroupTable')->where('GroupID', $request->GroupID)->first();
        $canAccessGroup = DB::table('GroupQetaa')
            ->where('GroupID', $request->GroupID)
            ->whereIn('QetaaID', $this->servedQetaaIds(Auth::id()))
            ->exists();

        if (!$group || (int) $group->GroupTypeID !== 3 || !$canAccessGroup) {
            return response()->json(['error' => 'لا يمكنك تعديل هذه المجموعة.'], 403);
        }

        DB::table('PersonGroup')
            ->where('PersonID', $request->PersonID)
            ->where('GroupID',  $request->GroupID)
            ->delete();

        return response()->json(['success' => true]);
    }
}
