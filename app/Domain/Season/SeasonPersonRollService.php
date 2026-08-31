<?php

namespace App\Domain\Season;

use App\Domain\Enrolment\LiveFormQetaaResolver;
use App\Support\LookupCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use stdClass;

class SeasonPersonRollService
{
    public const STATUS_APPLIED = 'applied';

    public const STATUS_ROLLED_BACK = 'rolled_back';

    public const JUMP_SANA_ONLY = 'sana_only';

    public const JUMP_QETAA_SAME_STAGE = 'qetaa_same_stage';

    public const JUMP_QETAA_CROSS = 'qetaa_cross';

    public const JUMP_TO_EADAD_QADA = 'to_eadad_qada';

    public function __construct(
        private readonly LiveFormQetaaResolver $qetaaResolver = new LiveFormQetaaResolver,
    ) {}

    /**
     * Ordered SanaMarhala IDs from DB (fallback 3..21).
     *
     * @return list<int>
     */
    public function sanaLadder(): array
    {
        $ids = DB::table('SanaMarhala')
            ->orderBy('MarhalaID')
            ->orderBy('SanaID')
            ->pluck('SanaMarhalaID')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return $ids !== [] ? $ids : range(3, 21);
    }

    public function nextSanaMarhalaId(int $currentId): ?int
    {
        $ladder = $this->sanaLadder();
        $index = array_search($currentId, $ladder, true);

        if ($index === false) {
            return null;
        }

        return $ladder[$index + 1] ?? null;
    }

    /**
     * Preview planned changes (no writes).
     *
     * @return array{rows: list<array<string, mixed>>, summary: array<string, int>, blocked_reason: ?string}
     */
    public function preview(?int $seasonId = null): array
    {
        $blocked = $this->applyBlockedReason($seasonId);
        $rows = $this->buildPlanRows();

        return [
            'rows' => $rows,
            'summary' => $this->summarize($rows),
            'blocked_reason' => $blocked,
        ];
    }

    /**
     * @return array{batch_id: int, summary: array<string, int>}
     */
    public function apply(int $seasonId, ?int $ranBy): array
    {
        if ($reason = $this->applyBlockedReason($seasonId)) {
            throw new RuntimeException($reason);
        }

        $rows = $this->buildPlanRows();
        if ($rows === []) {
            throw new RuntimeException('No persons eligible for season roll.');
        }

        return DB::transaction(function () use ($seasonId, $ranBy, $rows) {
            $summary = $this->summarize($rows);

            $batchId = (int) DB::table('season_person_roll_batches')->insertGetId([
                'season_id' => $seasonId,
                'ran_by' => $ranBy,
                'status' => self::STATUS_APPLIED,
                'persons_count' => $summary['persons'],
                'qetaa_changed_count' => $summary['qetaa_changed'],
                'groups_cleared_count' => $summary['groups_cleared'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($rows as $row) {
                $clearedGroups = [];
                $qetaaChanged = (int) $row['old_qetaa_id'] !== (int) $row['new_qetaa_id']
                    && $row['new_qetaa_id'] !== null;

                if ($qetaaChanged) {
                    $clearedGroups = DB::table('PersonGroup')
                        ->where('PersonID', $row['person_id'])
                        ->get()
                        ->map(fn ($g) => (array) $g)
                        ->values()
                        ->all();
                }

                DB::table('season_person_roll_snapshots')->insert([
                    'batch_id' => $batchId,
                    'person_id' => $row['person_id'],
                    'old_sana_marhala_id' => $row['old_sana_marhala_id'],
                    'new_sana_marhala_id' => $row['new_sana_marhala_id'],
                    'old_qetaa_id' => $row['old_qetaa_id'],
                    'new_qetaa_id' => $row['new_qetaa_id'],
                    'cleared_person_group_json' => $clearedGroups === [] ? null : json_encode($clearedGroups),
                    'jump_type' => $row['jump_type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->replaceSanaMarhala((int) $row['person_id'], (int) $row['new_sana_marhala_id']);

                if ($qetaaChanged) {
                    $this->replaceQetaa((int) $row['person_id'], (int) $row['new_qetaa_id']);
                    DB::table('PersonGroup')->where('PersonID', $row['person_id'])->delete();
                }
            }

            return [
                'batch_id' => $batchId,
                'summary' => $summary,
            ];
        });
    }

    public function rollback(int $batchId): void
    {
        DB::transaction(function () use ($batchId) {
            $batch = DB::table('season_person_roll_batches')->where('id', $batchId)->lockForUpdate()->first();
            if (! $batch) {
                throw new InvalidArgumentException('Roll batch not found.');
            }
            if ($batch->status !== self::STATUS_APPLIED) {
                throw new RuntimeException('This batch is already rolled back.');
            }

            $snapshots = DB::table('season_person_roll_snapshots')
                ->where('batch_id', $batchId)
                ->orderBy('id')
                ->get();

            foreach ($snapshots as $snap) {
                if ($snap->old_sana_marhala_id) {
                    $this->replaceSanaMarhala((int) $snap->person_id, (int) $snap->old_sana_marhala_id);
                }

                $qetaaChanged = $snap->old_qetaa_id !== null
                    && $snap->new_qetaa_id !== null
                    && (int) $snap->old_qetaa_id !== (int) $snap->new_qetaa_id;

                if ($qetaaChanged) {
                    $this->replaceQetaa((int) $snap->person_id, (int) $snap->old_qetaa_id);

                    DB::table('PersonGroup')->where('PersonID', $snap->person_id)->delete();

                    $groups = $snap->cleared_person_group_json
                        ? json_decode($snap->cleared_person_group_json, true)
                        : [];

                    foreach ($groups as $group) {
                        DB::table('PersonGroup')->insert([
                            'PersonID' => $group['PersonID'],
                            'GroupID' => $group['GroupID'],
                            'GroupRoleID' => $group['GroupRoleID'],
                        ]);
                    }
                }
            }

            DB::table('season_person_roll_batches')
                ->where('id', $batchId)
                ->update([
                    'status' => self::STATUS_ROLLED_BACK,
                    'updated_at' => now(),
                ]);
        });
    }

    public function openAppliedBatchForSeason(int $seasonId): ?stdClass
    {
        return DB::table('season_person_roll_batches')
            ->where('season_id', $seasonId)
            ->where('status', self::STATUS_APPLIED)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function history(?int $seasonId = null): Collection
    {
        $query = DB::table('season_person_roll_batches as b')
            ->leftJoin('Season as s', 's.SeasonID', '=', 'b.season_id')
            ->leftJoin('PersonInformation as p', 'p.PersonID', '=', 'b.ran_by')
            ->select(
                'b.*',
                's.SeasonName',
                's.SeasonYear',
                DB::raw("TRIM(CONCAT(COALESCE(p.FirstName,''),' ',COALESCE(p.SecondName,''),' ',COALESCE(p.ThirdName,''))) as RanByName")
            )
            ->orderByDesc('b.id');

        if ($seasonId) {
            $query->where('b.season_id', $seasonId);
        }

        return $query->get();
    }

    /**
     * Pure decision helper for tests / planning a single person.
     *
     * @return array{new_sana_marhala_id: ?int, new_qetaa_id: ?int, jump_type: ?string, skip: bool, skip_reason: ?string}
     */
    public function planPerson(
        ?int $oldSanaId,
        ?int $oldQetaaId,
        string $gender,
    ): array {
        if ($oldSanaId === null) {
            return [
                'new_sana_marhala_id' => null,
                'new_qetaa_id' => $oldQetaaId,
                'jump_type' => null,
                'skip' => true,
                'skip_reason' => 'missing_sana',
            ];
        }

        $newSanaId = $this->nextSanaMarhalaId($oldSanaId);
        if ($newSanaId === null) {
            return [
                'new_sana_marhala_id' => null,
                'new_qetaa_id' => $oldQetaaId,
                'jump_type' => null,
                'skip' => true,
                'skip_reason' => 'already_graduate',
            ];
        }

        $newQetaaId = $this->resolveNewQetaa($oldSanaId, $newSanaId, $oldQetaaId, $gender);
        $jumpType = $this->jumpType($oldSanaId, $newSanaId, $oldQetaaId, $newQetaaId);

        return [
            'new_sana_marhala_id' => $newSanaId,
            'new_qetaa_id' => $newQetaaId,
            'jump_type' => $jumpType,
            'skip' => false,
            'skip_reason' => null,
        ];
    }

    private function resolveNewQetaa(?int $oldSanaId, int $newSanaId, ?int $oldQetaaId, string $gender): ?int
    {
        if ($oldQetaaId !== null && in_array($oldQetaaId, LiveFormQetaaResolver::FROZEN_QETAA_IDS, true)) {
            return $oldQetaaId;
        }

        // Secondary 3 → university 1: enter اعداد قادة
        if ($oldSanaId !== null && $oldSanaId <= 14 && $newSanaId >= 15) {
            return LiveFormQetaaResolver::QETAA_EADAD_QADA;
        }

        if ($oldQetaaId !== null && in_array($oldQetaaId, LiveFormQetaaResolver::YOUTH_QETAA_IDS, true)) {
            if ($newSanaId >= 15) {
                return LiveFormQetaaResolver::QETAA_EADAD_QADA;
            }

            $resolved = $this->qetaaResolver->resolveYouthSectorId($newSanaId, $gender);

            return $resolved ?? $oldQetaaId;
        }

        return $oldQetaaId;
    }

    private function jumpType(?int $oldSana, int $newSana, ?int $oldQetaa, ?int $newQetaa): string
    {
        if ($newQetaa === LiveFormQetaaResolver::QETAA_EADAD_QADA
            && $oldQetaa !== LiveFormQetaaResolver::QETAA_EADAD_QADA
            && $oldSana !== null
            && $oldSana <= 14
            && $newSana >= 15) {
            return self::JUMP_TO_EADAD_QADA;
        }

        if ($oldQetaa === $newQetaa || $newQetaa === null) {
            return self::JUMP_SANA_ONLY;
        }

        $oldBand = $this->youthBand((int) $oldSana);
        $newBand = $this->youthBand($newSana);

        if ($oldBand !== null && $newBand !== null && $oldBand !== $newBand) {
            return self::JUMP_QETAA_CROSS;
        }

        return self::JUMP_QETAA_SAME_STAGE;
    }

    private function youthBand(int $sanaId): ?string
    {
        if ($sanaId >= 3 && $sanaId <= 4) {
            return 'baraem';
        }
        if ($sanaId >= 5 && $sanaId <= 8) {
            return 'ashbal_zahrat';
        }
        if ($sanaId >= 9 && $sanaId <= 11) {
            return 'kashafa_morshedat';
        }
        if ($sanaId >= 12 && $sanaId <= 14) {
            return 'motakadem_raedat';
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildPlanRows(): array
    {
        $sanaNames = LookupCache::all('SanaMarhala')->pluck('SanaMarhalaName', 'SanaMarhalaID');
        $qetaaNames = LookupCache::all('Qetaa')->pluck('QetaaName', 'QetaaID');

        $persons = DB::table('PersonInformation as pi')
            ->leftJoin('PersonSanaMarhala as psm', 'psm.PersonID', '=', 'pi.PersonID')
            ->leftJoin('PersonQetaa as pq', 'pq.PersonID', '=', 'pi.PersonID')
            ->select(
                'pi.PersonID',
                'pi.FirstName',
                'pi.SecondName',
                'pi.ThirdName',
                'pi.Gender',
                'psm.SanaMarhalaID',
                'pq.QetaaID'
            )
            ->orderBy('pi.PersonID')
            ->get();

        $personsWithGroups = DB::table('PersonGroup')
            ->distinct()
            ->pluck('PersonID')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $rows = [];

        foreach ($persons as $person) {
            $oldSana = $person->SanaMarhalaID !== null ? (int) $person->SanaMarhalaID : null;
            $oldQetaa = $person->QetaaID !== null ? (int) $person->QetaaID : null;
            $gender = $person->Gender === 'Female' ? 'Female' : 'Male';

            $plan = $this->planPerson($oldSana, $oldQetaa, $gender);
            if ($plan['skip']) {
                continue;
            }

            $qetaaChanged = $oldQetaa !== $plan['new_qetaa_id'];
            $willClearGroups = $qetaaChanged && $personsWithGroups->has((int) $person->PersonID);

            $rows[] = [
                'person_id' => (int) $person->PersonID,
                'person_name' => trim(implode(' ', array_filter([
                    $person->FirstName,
                    $person->SecondName,
                    $person->ThirdName,
                ]))),
                'gender' => $gender,
                'old_sana_marhala_id' => $oldSana,
                'new_sana_marhala_id' => $plan['new_sana_marhala_id'],
                'old_sana_name' => $oldSana ? ($sanaNames[$oldSana] ?? (string) $oldSana) : '—',
                'new_sana_name' => $sanaNames[$plan['new_sana_marhala_id']] ?? (string) $plan['new_sana_marhala_id'],
                'old_qetaa_id' => $oldQetaa,
                'new_qetaa_id' => $plan['new_qetaa_id'],
                'old_qetaa_name' => $oldQetaa ? ($qetaaNames[$oldQetaa] ?? (string) $oldQetaa) : '—',
                'new_qetaa_name' => $plan['new_qetaa_id']
                    ? ($qetaaNames[$plan['new_qetaa_id']] ?? (string) $plan['new_qetaa_id'])
                    : '—',
                'qetaa_changed' => $qetaaChanged,
                'will_clear_groups' => $willClearGroups,
                'jump_type' => $plan['jump_type'],
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<string, int>
     */
    private function summarize(array $rows): array
    {
        return [
            'persons' => count($rows),
            'qetaa_changed' => count(array_filter($rows, fn ($r) => ! empty($r['qetaa_changed']))),
            'groups_cleared' => count(array_filter($rows, fn ($r) => ! empty($r['will_clear_groups']))),
            'to_eadad_qada' => count(array_filter($rows, fn ($r) => ($r['jump_type'] ?? null) === self::JUMP_TO_EADAD_QADA)),
            'qetaa_cross' => count(array_filter($rows, fn ($r) => ($r['jump_type'] ?? null) === self::JUMP_QETAA_CROSS)),
        ];
    }

    private function applyBlockedReason(?int $seasonId): ?string
    {
        if (! $seasonId) {
            return 'No active season selected.';
        }

        if ($this->openAppliedBatchForSeason($seasonId)) {
            return 'An applied roll already exists for this season. Rollback first before applying again.';
        }

        return null;
    }

    private function replaceSanaMarhala(int $personId, int $sanaMarhalaId): void
    {
        DB::table('PersonSanaMarhala')->where('PersonID', $personId)->delete();
        DB::table('PersonSanaMarhala')->insert([
            'PersonID' => $personId,
            'SanaMarhalaID' => $sanaMarhalaId,
        ]);
    }

    private function replaceQetaa(int $personId, int $qetaaId): void
    {
        DB::table('PersonQetaa')->where('PersonID', $personId)->delete();
        DB::table('PersonQetaa')->insert([
            'PersonID' => $personId,
            'QetaaID' => $qetaaId,
        ]);
    }
}
