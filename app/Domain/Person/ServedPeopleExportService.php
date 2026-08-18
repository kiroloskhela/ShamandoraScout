<?php

namespace App\Domain\Person;

use App\Domain\Authz\PermissionService;
use App\Models\User;
use App\Policies\TreePolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ServedPeopleExportService
{
    public function __construct(
        private readonly PermissionService $permissions,
        private readonly TreePolicy $tree,
    ) {}

    /**
     * @return Collection<int, object{QetaaID: int, QetaaName: string|null}>
     */
    public function allowedQetaas(User $user): Collection
    {
        $ids = $this->allowedQetaaIds($user);
        if ($ids === []) {
            return collect();
        }

        return DB::table('Qetaa')
            ->whereIn('QetaaID', $ids)
            ->orderBy('QetaaName')
            ->get(['QetaaID', 'QetaaName']);
    }

    public function canExportQetaa(User $user, int $qetaaId): bool
    {
        return in_array($qetaaId, $this->allowedQetaaIds($user), true);
    }

    /**
     * @return array{sheets: list<array{title: string, rows: list<array<string, mixed>>}>, people_count: int}
     */
    public function build(int $qetaaId, int $seasonId): array
    {
        $qetaaName = (string) (DB::table('Qetaa')->where('QetaaID', $qetaaId)->value('QetaaName') ?? '');
        $people = $this->peopleInQetaa($qetaaId, $qetaaName);
        $personIds = $people->pluck('PersonID')->map(fn ($id) => (int) $id)->all();

        return [
            'people_count' => count($personIds),
            'sheets' => [
                [
                    'title' => 'البيانات الشخصية',
                    'rows' => $this->personRows($people),
                ],
                [
                    'title' => 'الحساسية والتاريخ الطبي',
                    'rows' => $this->medicalRows($people, $personIds),
                ],
                [
                    'title' => 'الأسئلة والأجوبة',
                    'rows' => $this->questionRows($people, $personIds, $qetaaId, $qetaaName),
                ],
                [
                    'title' => 'الحضور',
                    'rows' => $this->attendanceRows($people, $personIds, $qetaaId, $seasonId),
                ],
            ],
        ];
    }

    /**
     * SuperAdmin: every Qetaa. Others: sectors from PersonGroup.
     * web.people.manage (AdminQetaa) also includes their PersonQetaa rows.
     *
     * @return list<int>
     */
    private function allowedQetaaIds(User $user): array
    {
        if ($this->permissions->isSuperAdmin($user)) {
            return DB::table('Qetaa')
                ->pluck('QetaaID')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $ids = $this->tree->servedQetaaIds((int) $user->PersonID);

        if ($this->permissions->userCan($user, 'web.people.manage')) {
            $ids = $ids->merge(
                DB::table('PersonQetaa')
                    ->where('PersonID', $user->PersonID)
                    ->pluck('QetaaID')
                    ->map(fn ($id) => (int) $id)
            );
        }

        return $ids->unique()->values()->all();
    }

    /**
     * @return Collection<int, object>
     */
    private function peopleInQetaa(int $qetaaId, string $qetaaName): Collection
    {
        return DB::table('PersonInformation as pi')
            ->join('PersonQetaa as pq', 'pq.PersonID', '=', 'pi.PersonID')
            ->leftJoin('PersonSanaMarhala as psm', 'psm.PersonID', '=', 'pi.PersonID')
            ->leftJoin('SanaMarhala as sm', 'sm.SanaMarhalaID', '=', 'psm.SanaMarhalaID')
            ->leftJoin('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
            ->where('pq.QetaaID', $qetaaId)
            ->orderBy('pi.ShamandoraCode')
            ->orderBy('pi.PersonID')
            ->select(
                'pi.PersonID',
                'pi.ShamandoraCode',
                'pi.FirstName',
                'pi.SecondName',
                'pi.ThirdName',
                'pi.FourthName',
                'pi.ScoutJoiningYear',
                'pi.RaqamQawmy',
                'sm.SanaMarhalaName',
                'ppn.PersonPersonalMobileNumber',
                'ppn.MotherMobileNumber'
            )
            ->get()
            // Phone / stage joins can duplicate a person; keep one row per PersonID.
            ->unique('PersonID')
            ->values()
            ->map(function ($row) use ($qetaaName) {
                $row->QetaaName = $qetaaName;

                return $row;
            });
    }

    /**
     * @param  Collection<int, object>  $people
     * @return list<array<string, mixed>>
     */
    private function personRows(Collection $people): array
    {
        return $people->map(fn ($p) => [
            'PersonID' => $p->PersonID,
            'ShamandoraCode' => $p->ShamandoraCode,
            'FirstName' => $p->FirstName,
            'SecondName' => $p->SecondName,
            'ThirdName' => $p->ThirdName,
            'FourthName' => $p->FourthName,
            'QetaaName' => $p->QetaaName,
            'ScoutJoiningYear' => $p->ScoutJoiningYear ?? '',
            'SanaMarhalaName' => $p->SanaMarhalaName ?? '',
            'RaqamQawmy' => $p->RaqamQawmy ?? '',
            'PersonPersonalMobileNumber' => $p->PersonPersonalMobileNumber ?? '',
            'MotherMobileNumber' => $p->MotherMobileNumber ?? '',
        ])->all();
    }

    /**
     * @param  Collection<int, object>  $people
     * @param  list<int>  $personIds
     * @return list<array<string, mixed>>
     */
    private function medicalRows(Collection $people, array $personIds): array
    {
        if ($personIds === []) {
            return [];
        }

        $allergies = DB::table('PeopleAllergies')
            ->whereIn('PersonID', $personIds)
            ->get(['PersonID', 'AllergyType', 'AllergyName']);

        $history = DB::table('PeopleMedicalHistory')
            ->whereIn('PersonID', $personIds)
            ->get(['PersonID', 'Disease', 'Medication', 'HasEmergencyCase', 'EmergencyDetails']);

        $food = [];
        $medicine = [];
        foreach ($allergies as $row) {
            $id = (int) $row->PersonID;
            $name = trim((string) $row->AllergyName);
            if ($name === '') {
                continue;
            }
            if ($row->AllergyType === 'Food') {
                $food[$id][] = $name;
            } elseif ($row->AllergyType === 'Medicine') {
                $medicine[$id][] = $name;
            }
        }

        $diseases = [];
        $medications = [];
        $emergency = [];
        $emergencyDetails = [];
        foreach ($history as $row) {
            $id = (int) $row->PersonID;
            if (trim((string) $row->Disease) !== '') {
                $diseases[$id][] = trim((string) $row->Disease);
            }
            if (trim((string) $row->Medication) !== '') {
                $medications[$id][] = trim((string) $row->Medication);
            }
            if (! empty($row->HasEmergencyCase)) {
                $emergency[$id] = 1;
            }
            if (trim((string) $row->EmergencyDetails) !== '') {
                $emergencyDetails[$id][] = trim((string) $row->EmergencyDetails);
            }
        }

        $rows = [];
        foreach ($people as $p) {
            $id = (int) $p->PersonID;
            $foodList = isset($food[$id]) ? implode(' | ', array_unique($food[$id])) : '';
            $medList = isset($medicine[$id]) ? implode(' | ', array_unique($medicine[$id])) : '';
            $diseaseList = isset($diseases[$id]) ? implode(' | ', array_unique($diseases[$id])) : '';
            // Same filter as the previous export: allergies or disease only (not medication-only).
            if ($foodList === '' && $medList === '' && $diseaseList === '') {
                continue;
            }

            $rows[] = [
                'PersonID' => $p->PersonID,
                'ShamandoraCode' => $p->ShamandoraCode,
                'FirstName' => $p->FirstName,
                'SecondName' => $p->SecondName,
                'ThirdName' => $p->ThirdName,
                'FourthName' => $p->FourthName,
                'QetaaName' => $p->QetaaName,
                'FoodAllergies' => $foodList,
                'MedicineAllergies' => $medList,
                'Diseases' => $diseaseList,
                'Medications' => isset($medications[$id]) ? implode(' | ', array_unique($medications[$id])) : '',
                'HasEmergencyCase' => $emergency[$id] ?? '',
                'EmergencyDetails' => isset($emergencyDetails[$id]) ? implode(' | ', array_unique($emergencyDetails[$id])) : '',
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, object>  $people
     * @param  list<int>  $personIds
     * @return list<array<string, mixed>>
     */
    private function questionRows(Collection $people, array $personIds, int $qetaaId, string $qetaaName): array
    {
        $questions = DB::table('MarhalaEntryQuestions')
            ->where('QetaaID', $qetaaId)
            ->orderBy('QuestionID')
            ->get(['QuestionID', 'QuestionText']);

        $questionHeaders = [];
        $usedHeaders = [];
        foreach ($questions as $question) {
            $label = trim((string) $question->QuestionText);
            if ($label === '') {
                $label = 'Question '.$question->QuestionID;
            }
            if (isset($usedHeaders[$label])) {
                $label .= ' #'.$question->QuestionID;
            }
            $usedHeaders[$label] = true;
            $questionHeaders[(int) $question->QuestionID] = $label;
        }

        $answers = [];
        if ($personIds !== [] && $questions->isNotEmpty()) {
            foreach (DB::table('PersonEntryQuestions')
                ->whereIn('PersonID', $personIds)
                ->whereIn('QuestionID', $questions->pluck('QuestionID'))
                ->get(['PersonID', 'QuestionID', 'Answer']) as $row) {
                $answers[(int) $row->PersonID][(int) $row->QuestionID] = $row->Answer;
            }
        }

        return $people->map(function ($p) use ($questionHeaders, $answers, $qetaaName) {
            $row = [
                'PersonID' => $p->PersonID,
                'ShamandoraCode' => $p->ShamandoraCode,
                'FirstName' => $p->FirstName,
                'SecondName' => $p->SecondName,
                'ThirdName' => $p->ThirdName,
                'FourthName' => $p->FourthName,
                'QetaaName' => $qetaaName,
            ];
            $personAnswers = $answers[(int) $p->PersonID] ?? [];
            foreach ($questionHeaders as $questionId => $label) {
                $row[$label] = $personAnswers[$questionId] ?? '';
            }

            return $row;
        })->all();
    }

    /**
     * @param  Collection<int, object>  $people
     * @param  list<int>  $personIds
     * @return list<array<string, mixed>>
     */
    private function attendanceRows(Collection $people, array $personIds, int $qetaaId, int $seasonId): array
    {
        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->join('EventQetaa as eq', 'eq.EventID', '=', 'e.EventID')
            ->leftJoin('EventType as et', 'et.EventTypeID', '=', 'e.EventTypeID')
            ->where('se.SeasonID', $seasonId)
            ->where('eq.QetaaID', $qetaaId)
            ->orderBy('e.EventStartDate')
            ->orderBy('se.SeasonEventID')
            ->select(
                'se.SeasonEventID',
                'e.EventName',
                'e.EventStartDate',
                DB::raw('COALESCE(et.TakesReservation, 0) as TakesReservation')
            )
            ->get()
            ->unique('SeasonEventID')
            ->values();

        $patrols = $this->patrolNames($personIds, $qetaaId);
        $eventIds = $events->pluck('SeasonEventID')->map(fn ($id) => (int) $id)->all();
        $reservationIds = $events->filter(fn ($e) => (int) $e->TakesReservation === 1)
            ->pluck('SeasonEventID')
            ->map(fn ($id) => (int) $id)
            ->all();
        $regularIds = array_values(array_diff($eventIds, $reservationIds));

        $status = [];
        if ($personIds !== [] && $regularIds !== []) {
            foreach (DB::table('Attendance')
                ->whereIn('SeasonEventID', $regularIds)
                ->whereIn('ServedID', $personIds)
                ->get(['SeasonEventID', 'ServedID', 'AttendanceStatus']) as $row) {
                $status[(int) $row->ServedID][(int) $row->SeasonEventID] = (string) $row->AttendanceStatus;
            }
        }

        if ($personIds !== [] && $reservationIds !== []) {
            $bookings = DB::table('SeasonEventParticipantFinance as b')
                ->leftJoin('SeasonEventBookingAttendance as a', 'a.SeasonEventParticipantFinanceID', '=', 'b.SeasonEventParticipantFinanceID')
                ->where('b.IsRefunded', 0)
                ->whereIn('b.SeasonEventID', $reservationIds)
                ->whereIn('b.PersonID', $personIds)
                ->orderByDesc('b.SeasonEventParticipantFinanceID')
                ->get(['b.PersonID', 'b.SeasonEventID', 'a.AttendanceStatus']);

            foreach ($bookings as $row) {
                $pid = (int) $row->PersonID;
                $eid = (int) $row->SeasonEventID;
                if (isset($status[$pid][$eid])) {
                    continue;
                }
                $status[$pid][$eid] = $row->AttendanceStatus ? (string) $row->AttendanceStatus : '';
            }
        }

        $eventHeaders = [];
        $usedHeaders = [];
        foreach ($events as $event) {
            $header = trim((string) $event->EventName);
            if ($event->EventStartDate) {
                $header .= ' ('.$event->EventStartDate.')';
            }
            if ($header === '' || isset($usedHeaders[$header])) {
                $header = ($header !== '' ? $header.' #' : '#').(int) $event->SeasonEventID;
            }
            $usedHeaders[$header] = true;
            $eventHeaders[(int) $event->SeasonEventID] = $header;
        }

        return $people->map(function ($p) use ($eventHeaders, $patrols, $status) {
            $pid = (int) $p->PersonID;
            $row = [
                'FullName' => trim(implode(' ', array_filter([
                    $p->FirstName,
                    $p->SecondName,
                    $p->ThirdName,
                    $p->FourthName,
                ], fn ($part) => trim((string) $part) !== ''))),
                'Tale3a' => $patrols[$pid] ?? '',
            ];
            foreach ($eventHeaders as $eventId => $header) {
                $row[$header] = $status[$pid][$eventId] ?? '';
            }

            return $row;
        })->all();
    }

    /**
     * @param  list<int>  $personIds
     * @return array<int, string>
     */
    private function patrolNames(array $personIds, int $qetaaId): array
    {
        if ($personIds === []) {
            return [];
        }

        $names = [];
        $rows = DB::table('PersonGroup as pg')
            ->join('GroupTable as g', 'g.GroupID', '=', 'pg.GroupID')
            ->join('GroupQetaa as gq', 'gq.GroupID', '=', 'g.GroupID')
            ->where('g.GroupTypeID', 3) // Tale3a / patrol
            ->where('gq.QetaaID', $qetaaId)
            ->whereIn('pg.PersonID', $personIds)
            ->orderBy('g.GroupName')
            ->get(['pg.PersonID', 'g.GroupName']);

        foreach ($rows as $row) {
            $name = trim((string) $row->GroupName);
            if ($name === '') {
                continue;
            }
            $names[(int) $row->PersonID][] = $name;
        }

        return array_map(
            fn (array $list) => implode(' | ', array_unique($list)),
            $names
        );
    }
}
