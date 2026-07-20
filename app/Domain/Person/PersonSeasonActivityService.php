<?php

namespace App\Domain\Person;

use App\Domain\Season\ActiveSeason;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PersonSeasonActivityService
{
    public function __construct(
        private readonly ActiveSeason $activeSeason = new ActiveSeason,
    ) {}

    /**
     * @return Collection<int, object>
     */
    public function seasons(): Collection
    {
        return DB::table('Season')
            ->orderByDesc('IsActive')
            ->orderByDesc('SeasonYear')
            ->orderBy('SeasonName')
            ->get();
    }

    public function resolveSeasonId(?int $requestedSeasonId): ?int
    {
        if ($requestedSeasonId && DB::table('Season')->where('SeasonID', $requestedSeasonId)->exists()) {
            return $requestedSeasonId;
        }

        return $this->activeSeason->id();
    }

    /**
     * @return array{
     *   season: ?object,
     *   attendance: array{events: Collection, summary: array{total: int, present: int, absent: int, excused: int, rate: float|int}},
     *   exams: Collection,
     *   finances: Collection
     * }
     */
    public function forPerson(int $personId, ?int $seasonId): array
    {
        $season = $seasonId
            ? DB::table('Season')->where('SeasonID', $seasonId)->first()
            : null;

        return [
            'season' => $season,
            'attendance' => $this->loadAttendance($personId, $seasonId),
            'exams' => $this->loadExams($personId, $seasonId),
            'finances' => $this->loadFinances($personId, $seasonId),
        ];
    }

    /**
     * @return array{events: Collection, summary: array{total: int, present: int, absent: int, excused: int, rate: float|int}}
     */
    public function loadAttendance(int $personId, ?int $seasonId): array
    {
        if (! $seasonId) {
            return [
                'events' => collect(),
                'summary' => ['total' => 0, 'present' => 0, 'absent' => 0, 'excused' => 0, 'rate' => 0],
            ];
        }

        $events = DB::table('PersonQetaa as pq')
            ->join('EventQetaa as eq', 'eq.QetaaID', '=', 'pq.QetaaID')
            ->join('SeasonEvent as se', 'se.EventID', '=', 'eq.EventID')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->join('Season as s', 's.SeasonID', '=', 'se.SeasonID')
            ->leftJoin('Attendance as a', function ($join) {
                $join->on('a.SeasonEventID', '=', 'se.SeasonEventID')
                    ->on('a.ServedID', '=', 'pq.PersonID');
            })
            ->where('pq.PersonID', $personId)
            ->where('se.SeasonID', $seasonId)
            ->select(
                'se.SeasonEventID',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
                's.SeasonName',
                's.SeasonYear',
                DB::raw("COALESCE(a.AttendanceStatus, 'absent') AS Status"),
                'a.Excuse'
            )
            ->orderByDesc('e.EventStartDate')
            ->get();

        $total = $events->count();
        $present = $events->where('Status', 'present')->count();
        $absent = $events->where('Status', 'absent')->count();
        $excused = $events->where('Status', 'excused')->count();

        return [
            'events' => $events,
            'summary' => [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'excused' => $excused,
                'rate' => $total ? round($present / $total * 100, 1) : 0,
            ],
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function loadExams(int $personId, ?int $seasonId): Collection
    {
        if (! $seasonId || ! Schema::hasTable('PersonExamMark')) {
            return collect();
        }

        $query = DB::table('PersonExamMark as em')
            ->leftJoin('Qetaa as q', 'q.QetaaID', '=', 'em.QetaaID')
            ->leftJoin('SanaMarhala as sm', 'sm.SanaMarhalaID', '=', 'em.SanaMarhalaID')
            ->where('em.PersonID', $personId)
            ->select(
                'em.ExamMarkID',
                'em.TheoreticalMark',
                'em.PracticalMark',
                'em.ExamDate',
                'em.Note',
                'q.QetaaName',
                'sm.SanaMarhalaName',
                DB::raw('(COALESCE(em.TheoreticalMark, 0) + COALESCE(em.PracticalMark, 0)) AS TotalMark')
            )
            ->orderByDesc('em.ExamDate')
            ->orderByDesc('em.ExamMarkID');

        if (Schema::hasColumn('PersonExamMark', 'SeasonID')) {
            $query->where('em.SeasonID', $seasonId);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function loadFinances(int $personId, ?int $seasonId): Collection
    {
        if (! $seasonId || ! Schema::hasTable('SeasonEventParticipantFinance')) {
            return collect();
        }

        return DB::table('SeasonEventParticipantFinance as f')
            ->join('SeasonEvent as se', 'se.SeasonEventID', '=', 'f.SeasonEventID')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('f.PersonID', $personId)
            ->where('se.SeasonID', $seasonId)
            ->select(
                'f.SeasonEventParticipantFinanceID',
                'e.EventName',
                'e.EventStartDate',
                'f.FinalRequiredAmount',
                'f.AmountPaid',
                'f.RemainingAmount',
                'f.IsRefunded',
                'f.ShirtSize'
            )
            ->orderByDesc('e.EventStartDate')
            ->get();
    }
}
