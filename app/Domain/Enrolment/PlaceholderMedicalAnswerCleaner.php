<?php

namespace App\Domain\Enrolment;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlaceholderMedicalAnswerCleaner
{
    private const TEXT_COLUMNS = [
        'AllergyFood',
        'AllergyMedicine',
        'MedicalDiseases',
        'MedicalMedications',
        'EmergencyDetails',
    ];

    private const ENROLMENT_TABLES = [
        'NewUsersInformation',
        'NewUsersInformationWaitinglist',
    ];

    /** @var array<string, mixed> */
    private array $emptyValues = [];

    public function __construct(private LiveFormFieldNormalizer $fields) {}

    /**
     * @return array{enrolment_updated: int, allergies_deleted: int, allergies_updated: int, history_deleted: int, history_updated: int}
     */
    public function run(): array
    {
        $enrolmentUpdated = 0;
        foreach (self::ENROLMENT_TABLES as $table) {
            $enrolmentUpdated += $this->cleanEnrolmentTable($table);
        }

        $allergyCounts = $this->cleanPeopleAllergies();
        $historyCounts = $this->cleanPeopleMedicalHistory();

        return [
            'enrolment_updated' => $enrolmentUpdated,
            'allergies_deleted' => $allergyCounts['deleted'],
            'allergies_updated' => $allergyCounts['updated'],
            'history_deleted' => $historyCounts['deleted'],
            'history_updated' => $historyCounts['updated'],
        ];
    }

    private function cleanEnrolmentTable(string $table): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'PersonID')) {
            return 0;
        }

        $columns = array_values(array_filter(
            self::TEXT_COLUMNS,
            fn (string $column) => Schema::hasColumn($table, $column)
        ));

        if ($columns === []) {
            return 0;
        }

        $updated = 0;
        $select = array_merge(['PersonID'], $columns);

        DB::table($table)
            ->orderBy('PersonID')
            ->select($select)
            ->chunk(200, function ($rows) use ($table, $columns, &$updated) {
                foreach ($rows as $row) {
                    $changes = [];
                    foreach ($columns as $column) {
                        $raw = $row->{$column} ?? null;
                        $cleaned = $this->fields->cleanList($raw === null ? null : (string) $raw);
                        if ($this->storedEqualsCleaned($raw, $cleaned)) {
                            continue;
                        }
                        $changes[$column] = $cleaned;
                    }
                    if ($changes === []) {
                        continue;
                    }
                    DB::table($table)->where('PersonID', $row->PersonID)->update($changes);
                    $updated++;
                }
            });

        return $updated;
    }

    /**
     * @return array{deleted: int, updated: int}
     */
    private function cleanPeopleAllergies(): array
    {
        $counts = ['deleted' => 0, 'updated' => 0];
        if (! Schema::hasTable('PeopleAllergies') || ! Schema::hasColumn('PeopleAllergies', 'AllergyName')) {
            return $counts;
        }

        $pk = $this->firstExistingColumn('PeopleAllergies', ['AllergyID', 'PeopleAllergyID']);
        $select = array_values(array_filter([$pk, 'PersonID', 'AllergyName']));

        $this->eachRow('PeopleAllergies', $pk, $select, function ($row) use ($pk, &$counts) {
            $cleaned = $this->fields->cleanList((string) ($row->AllergyName ?? ''));
            $query = $this->rowQuery('PeopleAllergies', $pk, $row, ['AllergyName']);

            if ($cleaned === null) {
                $query->delete();
                $counts['deleted']++;

                return;
            }

            if ($this->storedEqualsCleaned($row->AllergyName, $cleaned)) {
                return;
            }

            $query->update(['AllergyName' => $cleaned]);
            $counts['updated']++;
        });

        return $counts;
    }

    /**
     * @return array{deleted: int, updated: int}
     */
    private function cleanPeopleMedicalHistory(): array
    {
        $counts = ['deleted' => 0, 'updated' => 0];
        if (! Schema::hasTable('PeopleMedicalHistory')) {
            return $counts;
        }

        $pk = $this->firstExistingColumn('PeopleMedicalHistory', ['MedicalHistoryID', 'PeopleMedicalHistoryID']);
        $hasDisease = Schema::hasColumn('PeopleMedicalHistory', 'Disease');
        $hasMedication = Schema::hasColumn('PeopleMedicalHistory', 'Medication');
        $hasDetails = Schema::hasColumn('PeopleMedicalHistory', 'EmergencyDetails');
        $hasEmergency = Schema::hasColumn('PeopleMedicalHistory', 'HasEmergencyCase');

        $select = array_values(array_filter([
            $pk,
            'PersonID',
            $hasDisease ? 'Disease' : null,
            $hasMedication ? 'Medication' : null,
            $hasDetails ? 'EmergencyDetails' : null,
            $hasEmergency ? 'HasEmergencyCase' : null,
        ]));

        $this->eachRow('PeopleMedicalHistory', $pk, $select, function ($row) use ($pk, $hasDisease, $hasMedication, $hasDetails, $hasEmergency, &$counts) {
            $disease = $hasDisease ? $this->fields->cleanList($row->Disease === null ? null : (string) $row->Disease) : null;
            $medication = $hasMedication ? $this->fields->cleanList($row->Medication === null ? null : (string) $row->Medication) : null;
            $details = $hasDetails ? $this->fields->cleanList($row->EmergencyDetails === null ? null : (string) $row->EmergencyDetails) : null;
            $emergencyFlag = $hasEmergency ? (int) ($row->HasEmergencyCase ?? 0) : 0;

            $query = $this->rowQuery('PeopleMedicalHistory', $pk, $row);

            if ((! $hasDisease || $disease === null)
                && (! $hasMedication || $medication === null)
                && (! $hasDetails || $details === null)
                && $emergencyFlag === 0) {
                $query->delete();
                $counts['deleted']++;

                return;
            }

            $changes = [];
            if ($hasDisease && ! $this->storedEqualsCleaned($row->Disease, $disease)) {
                $changes['Disease'] = $disease ?? $this->emptyValue('PeopleMedicalHistory', 'Disease');
            }
            if ($hasMedication && ! $this->storedEqualsCleaned($row->Medication, $medication)) {
                $changes['Medication'] = $medication;
            }
            if ($hasDetails && ! $this->storedEqualsCleaned($row->EmergencyDetails, $details)) {
                $changes['EmergencyDetails'] = $details;
            }

            if ($changes === []) {
                return;
            }

            $query->update($changes);
            $counts['updated']++;
        });

        return $counts;
    }

    /**
     * @param  list<string>  $select
     * @param  callable(object): void  $fn
     */
    private function eachRow(string $table, ?string $pk, array $select, callable $fn): void
    {
        if ($pk !== null) {
            DB::table($table)
                ->orderBy($pk)
                ->select($select)
                ->chunkById(200, function ($rows) use ($fn) {
                    foreach ($rows as $row) {
                        $fn($row);
                    }
                }, $pk, $pk);

            return;
        }

        foreach (DB::table($table)->orderBy('PersonID')->select($select)->get() as $row) {
            $fn($row);
        }
    }

    /**
     * @param  list<string>  $extraEquals
     */
    private function rowQuery(string $table, ?string $pk, object $row, array $extraEquals = []): Builder
    {
        $query = DB::table($table);
        if ($pk !== null) {
            return $query->where($pk, $row->{$pk});
        }

        $query->where('PersonID', $row->PersonID);
        foreach ($extraEquals as $column) {
            $query->where($column, $row->{$column});
        }

        return $query;
    }

    private function storedEqualsCleaned(mixed $raw, ?string $cleaned): bool
    {
        if ($cleaned === null) {
            return $raw === null || trim((string) $raw) === '';
        }

        return trim((string) ($raw ?? '')) === $cleaned;
    }

    private function emptyValue(string $table, string $column): mixed
    {
        $key = $table.'.'.$column;
        if (array_key_exists($key, $this->emptyValues)) {
            return $this->emptyValues[$key];
        }

        $value = null;
        foreach (Schema::getColumns($table) as $meta) {
            if (($meta['name'] ?? '') === $column) {
                $value = ! empty($meta['nullable']) ? null : '';
                break;
            }
        }

        return $this->emptyValues[$key] = $value;
    }

    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}
