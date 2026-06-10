<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QetaaTreeApiController extends Controller
{
    public function auxiliary(Request $request)
    {
        $userId = Auth::id();
        $servedQetaaIds = $this->servedQetaaIds($userId);

        $servedQetaas = DB::table('Qetaa')
            ->whereIn('QetaaID', $servedQetaaIds)
            ->orderBy('QetaaID')
            ->get(['QetaaID', 'QetaaName']);

        $selectedQetaaId = $request->query('qetaa');

        if (!$selectedQetaaId && $servedQetaas->count() === 1) {
            $selectedQetaaId = $servedQetaas->first()->QetaaID;
        }

        if ($selectedQetaaId && !$servedQetaaIds->contains((int) $selectedQetaaId)) {
            $selectedQetaaId = null;
        }

        // ── Stage 1: no qetaa selected ────────────────────────────────────────
        if (!$selectedQetaaId) {
            return response()->json([
                'stage'         => 'select_qetaa',
                'served_qetaas' => $servedQetaas,
                'teams'         => [],
                'talaea'        => [],
                'stats'         => [
                    'qetaas' => $servedQetaas->count(),
                    'teams'  => 0,
                    'talaea' => 0,
                    'people' => 0,
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // ── Teams & direct-talaea count ───────────────────────────────────────
        $teams = DB::table('GroupTable as gt')
            ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
            ->where('gq.QetaaID', $selectedQetaaId)
            ->where('gt.GroupTypeID', 2)
            ->orderBy('gt.GroupName')
            ->get(['gt.GroupID', 'gt.GroupName']);

        $directTalaeaCount = DB::table('GroupTable as gt')
            ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
            ->where('gq.QetaaID', $selectedQetaaId)
            ->where('gt.GroupTypeID', 3)
            ->where(function ($q) {
                $q->whereNull('gt.IncludedUnderGroupID')
                    ->orWhere('gt.IncludedUnderGroupID', 0);
            })
            ->count();

        $selectedTeamId = $request->query('team');

        if (!$selectedTeamId && $directTalaeaCount > 0 && $teams->isEmpty()) {
            $selectedTeamId = 'direct';
        } elseif (!$selectedTeamId && $directTalaeaCount === 0 && $teams->count() === 1) {
            $selectedTeamId = $teams->first()->GroupID;
        }

        if (
            $selectedTeamId &&
            $selectedTeamId !== 'direct' &&
            !$teams->pluck('GroupID')->contains((int) $selectedTeamId)
        ) {
            $selectedTeamId = null;
        }

        // ── Stage 2: no team selected ─────────────────────────────────────────
        if (!$selectedTeamId) {
            return response()->json([
                'stage'               => 'select_team',
                'served_qetaas'       => $servedQetaas,
                'selected_qetaa_id'   => $selectedQetaaId,
                'teams'               => $teams,
                'direct_talaea_count' => $directTalaeaCount,
                'talaea'              => [],
                'stats'               => [
                    'qetaas' => $servedQetaas->count(),
                    'teams'  => $teams->count(),
                    'talaea' => 0,
                    'people' => 0,
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // ── Fetch talaea ──────────────────────────────────────────────────────
        if ($selectedTeamId === 'direct') {
            $selectedTeam = ['id' => 'direct', 'name' => 'الطلائع المباشرة'];

            $talaea = DB::table('GroupTable as gt')
                ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'gt.GroupID')
                ->where('gq.QetaaID', $selectedQetaaId)
                ->where('gt.GroupTypeID', 3)
                ->where(function ($q) {
                    $q->whereNull('gt.IncludedUnderGroupID')
                        ->orWhere('gt.IncludedUnderGroupID', 0);
                })
                ->orderBy('gt.GroupName')
                ->get(['gt.GroupID', 'gt.GroupName']);
        } else {
            $teamObj      = $teams->firstWhere('GroupID', (int) $selectedTeamId);
            $selectedTeam = [
                'id'   => $teamObj->GroupID,
                'name' => $teamObj->GroupName,
            ];

            $talaea = DB::table('GroupTable as gt')
                ->where('gt.GroupTypeID', 3)
                ->where('gt.IncludedUnderGroupID', $selectedTeamId)
                ->orderBy('gt.GroupName')
                ->get(['gt.GroupID', 'gt.GroupName']);
        }

        // ── Attach people to each taleia ──────────────────────────────────────
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

        $taleaWithPeople = $talaea->map(function ($taleia) use ($peopleByTaleia) {
            $people = $peopleByTaleia
                ->get($taleia->GroupID, collect())
                ->map(fn($p) => [
                    'person_id'       => $p->PersonID,
                    'first_name'      => $p->FirstName,
                    'second_name'     => $p->SecondName,
                    'full_name'       => trim("{$p->FirstName} {$p->SecondName}"),
                    'shamandora_code' => $p->ShamandoraCode,
                    'rotba_id'        => $p->RotbaID,
                    'rotba_name'      => $p->RotbaName,
                    'avatar_url'      => $p->PersonSystemImagePath
                                            ? asset('storage/' . $p->PersonSystemImagePath)
                                            : null,
                ]);

            return [
                'group_id'     => $taleia->GroupID,
                'group_name'   => $taleia->GroupName,
                'people_count' => $people->count(),
                'people'       => $people->values(),
            ];
        });

        $totalPeople = $taleaWithPeople->sum('people_count');

        return response()->json([
            'stage'               => 'results',
            'served_qetaas'       => $servedQetaas,
            'selected_qetaa_id'   => $selectedQetaaId,
            'teams'               => $teams,
            'direct_talaea_count' => $directTalaeaCount,
            'selected_team'       => $selectedTeam,
            'talaea'              => $taleaWithPeople->values(),
            'stats'               => [
                'qetaas' => $servedQetaas->count(),
                'teams'  => $teams->count(),
                'talaea' => $taleaWithPeople->count(),
                'people' => $totalPeople,
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE);
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
}