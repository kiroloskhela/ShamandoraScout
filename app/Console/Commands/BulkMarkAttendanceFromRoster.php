<?php

namespace App\Console\Commands;

use App\Services\AttendanceQrService;
use App\Services\BookingAttendanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkMarkAttendanceFromRoster extends Command
{
    protected $signature = 'attendance:bulk-mark
        {seasonEventId : SeasonEventID to mark attendance for}
        {path : Path to .xlsx or .json roster file}
        {--status=present : Attendance status (present|absent|outside|excused)}
        {--servent-id= : PersonID recorded as the servant who marked attendance}
        {--force-attendance-table : Write to Attendance even if the event takes reservations}
        {--dry-run : Resolve people and report without writing}';

    protected $description = 'Bulk-mark attendance from an Excel/JSON roster of Shamandora codes and names';

    public function handle(AttendanceQrService $qr, BookingAttendanceService $bookingAttendance): int
    {
        $seasonEventId = (int) $this->argument('seasonEventId');
        $path = (string) $this->argument('path');
        $status = (string) $this->option('status');
        $dryRun = (bool) $this->option('dry-run');
        $serventId = (int) ($this->option('servent-id') ?: 0);

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $event = DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->join('EventType as et', 'et.EventTypeID', '=', 'e.EventTypeID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->first([
                'se.SeasonEventID',
                'e.EventName',
                'et.EventTypeName',
                'et.TakesReservation',
            ]);

        if (! $event) {
            $this->error("SeasonEvent {$seasonEventId} not found.");

            return self::FAILURE;
        }

        $takesReservation = (bool) $event->TakesReservation
            && ! (bool) $this->option('force-attendance-table');
        $allowedStatuses = $takesReservation
            ? BookingAttendanceService::STATUSES
            : ['present', 'absent', 'excused'];

        if (! in_array($status, $allowedStatuses, true)) {
            $this->error("Invalid status '{$status}' for this event. Allowed: ".implode(', ', $allowedStatuses));

            return self::FAILURE;
        }

        if ($serventId <= 0) {
            $serventId = (int) (DB::table('PersonInformation')->orderBy('PersonID')->value('PersonID') ?: 0);
        }

        if ($serventId <= 0) {
            $this->error('Could not resolve --servent-id. Pass it explicitly.');

            return self::FAILURE;
        }

        $people = $this->loadPeople($path);
        if ($people === []) {
            $this->error('No people found in roster file.');

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Event: #%d %s (%s) | mode=%s | status=%s | people=%d | servent=%d%s',
            $seasonEventId,
            $event->EventName,
            $event->EventTypeName,
            $takesReservation ? 'booking' : 'attendance',
            $status,
            count($people),
            $serventId,
            $dryRun ? ' | DRY-RUN' : ''
        ));

        $ok = 0;
        $updated = 0;
        $created = 0;
        $missingPerson = [];
        $missingBooking = [];
        $ambiguous = [];

        foreach ($people as $person) {
            $resolved = $this->resolvePerson($person);
            if (($resolved['error'] ?? null) === 'ambiguous') {
                $ambiguous[] = $person + ['matches' => $resolved['matches'] ?? []];
                continue;
            }
            if (empty($resolved['person_id'])) {
                $missingPerson[] = $person;
                continue;
            }

            $personId = (int) $resolved['person_id'];

            if ($takesReservation) {
                $booking = $bookingAttendance->findActiveBooking($seasonEventId, AttendanceQrService::TYPE_PERSON, $personId);
                if (! $booking) {
                    $missingBooking[] = $person + ['person_id' => $personId];
                    continue;
                }

                if ($dryRun) {
                    $ok++;
                    continue;
                }

                $existing = DB::table('SeasonEventBookingAttendance')
                    ->where('SeasonEventParticipantFinanceID', $booking->SeasonEventParticipantFinanceID)
                    ->first();

                $now = now();
                if ($existing) {
                    DB::table('SeasonEventBookingAttendance')
                        ->where('SeasonEventBookingAttendanceID', $existing->SeasonEventBookingAttendanceID)
                        ->update([
                            'AttendanceStatus' => $status,
                            'ServentID' => $serventId,
                            'UpdatedAt' => $now,
                        ]);
                    $updated++;
                } else {
                    DB::table('SeasonEventBookingAttendance')->insert([
                        'SeasonEventParticipantFinanceID' => $booking->SeasonEventParticipantFinanceID,
                        'SeasonEventID' => $seasonEventId,
                        'AttendanceStatus' => $status,
                        'ServentID' => $serventId,
                        'CreatedAt' => $now,
                        'UpdatedAt' => $now,
                    ]);
                    $created++;
                }
                $ok++;
                continue;
            }

            if ($dryRun) {
                $ok++;
                continue;
            }

            $affected = DB::table('Attendance')->upsert(
                [[
                    'SeasonEventID' => $seasonEventId,
                    'ServedID' => $personId,
                    'ServentID' => $serventId,
                    'AttendanceStatus' => $status,
                    'Excuse' => null,
                ]],
                ['SeasonEventID', 'ServedID'],
                ['ServentID', 'AttendanceStatus', 'Excuse']
            );
            // MySQL upsert returns 1 for insert, 2 for update
            if ($affected === 1) {
                $created++;
            } else {
                $updated++;
            }
            $ok++;
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Resolved / marked', $ok],
                ['Created', $created],
                ['Updated', $updated],
                ['Missing person', count($missingPerson)],
                ['Missing booking', count($missingBooking)],
                ['Ambiguous name', count($ambiguous)],
            ]
        );

        if ($missingPerson !== []) {
            $this->warn('Missing person (no ShamandoraCode / name match):');
            foreach ($missingPerson as $row) {
                $this->line(sprintf('  - row %s | %s | %s', $row['row'] ?? '?', $row['code'] ?? '-', $row['name'] ?? '-'));
            }
        }

        if ($missingBooking !== []) {
            $this->warn('Person found but no active booking on this event:');
            foreach ($missingBooking as $row) {
                $this->line(sprintf(
                    '  - PersonID %s | %s | %s',
                    $row['person_id'],
                    $row['code'] ?? '-',
                    $row['name'] ?? '-'
                ));
            }
        }

        if ($ambiguous !== []) {
            $this->warn('Ambiguous name matches (skipped):');
            foreach ($ambiguous as $row) {
                $this->line(sprintf('  - %s => PersonIDs %s', $row['name'] ?? '-', implode(',', $row['matches'] ?? [])));
            }
        }

        if ($dryRun) {
            $this->comment('Dry run only — no attendance rows were written.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array{row?: int|string|null, team?: string|null, code?: string|null, name?: string|null}>
     */
    private function loadPeople(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'json') {
            $payload = json_decode((string) file_get_contents($path), true);
            if (! is_array($payload)) {
                return [];
            }
            $people = $payload['people'] ?? $payload;

            return array_values(array_filter(is_array($people) ? $people : [], 'is_array'));
        }

        $sheet = IOFactory::load($path)->getActiveSheet();
        $people = [];
        $currentTeam = null;

        foreach ($sheet->toArray(null, true, true, false) as $index => $vals) {
            $rowNum = $index + 1;
            $b = $vals[1] ?? null;
            $c = $vals[2] ?? null;
            $d = $vals[3] ?? null;

            if ($b === '#' && is_string($c) && trim($c) !== '') {
                $currentTeam = trim($c);
                continue;
            }

            if (is_string($c) && trim($c) !== '' && ! preg_match('/^SH-\d+/i', trim($c)) && ! is_numeric($b)) {
                $currentTeam = trim($c);
                continue;
            }

            if (! is_numeric($b)) {
                continue;
            }

            $code = (is_string($c) && preg_match('/^SH-\d+/i', trim($c))) ? strtoupper(trim($c)) : null;
            $name = is_string($d) ? trim($d) : null;
            if ($name === '') {
                $name = null;
            }

            if (! $code && ! $name) {
                continue;
            }

            $people[] = [
                'row' => $rowNum,
                'team' => $currentTeam,
                'code' => $code,
                'name' => $name,
            ];
        }

        return $people;
    }

    /**
     * @param  array{code?: string|null, name?: string|null}  $person
     * @return array{person_id: int|null, error?: string, matches?: list<int>}
     */
    private function resolvePerson(array $person): array
    {
        $code = isset($person['code']) && is_string($person['code']) ? strtoupper(trim($person['code'])) : null;
        if ($code) {
            $personId = DB::table('PersonInformation')
                ->where('ShamandoraCode', $code)
                ->value('PersonID');

            if ($personId) {
                return ['person_id' => (int) $personId];
            }

            // SH-01072 → 1072 fallback if ShamandoraCode column drifts
            if (preg_match('/^SH-0*(\d+)$/', $code, $m)) {
                $fallbackId = (int) $m[1];
                $exists = DB::table('PersonInformation')->where('PersonID', $fallbackId)->exists();
                if ($exists) {
                    return ['person_id' => $fallbackId];
                }
            }

            return ['person_id' => null];
        }

        $name = isset($person['name']) && is_string($person['name']) ? trim($person['name']) : '';
        if ($name === '') {
            return ['person_id' => null];
        }

        $parts = preg_split('/\s+/u', $name) ?: [];
        $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));

        $query = DB::table('PersonInformation')->select('PersonID');
        if (count($parts) >= 1) {
            $query->where('FirstName', $parts[0]);
        }
        if (count($parts) >= 2) {
            $query->where('SecondName', $parts[1]);
        }
        if (count($parts) >= 3) {
            $query->where('ThirdName', $parts[2]);
        }
        if (count($parts) >= 4) {
            $query->where('FourthName', $parts[3]);
        }

        $ids = $query->pluck('PersonID')->map(fn ($id) => (int) $id)->all();
        if (count($ids) === 1) {
            return ['person_id' => $ids[0]];
        }
        if (count($ids) > 1) {
            return ['person_id' => null, 'error' => 'ambiguous', 'matches' => $ids];
        }

        // Looser full-name concat match
        $ids = DB::table('PersonInformation')
            ->select('PersonID')
            ->whereRaw(
                "TRIM(CONCAT_WS(' ', FirstName, SecondName, ThirdName, FourthName)) = ?",
                [$name]
            )
            ->pluck('PersonID')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (count($ids) === 1) {
            return ['person_id' => $ids[0]];
        }
        if (count($ids) > 1) {
            return ['person_id' => null, 'error' => 'ambiguous', 'matches' => $ids];
        }

        return ['person_id' => null];
    }
}
