<?php

namespace App\Domain\EventProgram;

use App\Models\EventProgram;
use App\Models\EventProgramImportSession;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Re-pull sheet from saved Google URL and apply mission/game updates lightly.
 * Remembers previously matched leaders so small edits don't force full Q&A.
 */
final class EventProgramRefreshService
{
    public function __construct(
        private readonly EventProgramImporter $importer,
        private readonly GoogleSheetFetcher $sheets,
        private readonly EventProgramParser $parser,
        private readonly PersonResolver $people,
    ) {}

    /**
     * @return array{program: EventProgram, session: EventProgramImportSession, needs_review: bool, summary: string}
     */
    public function refreshFromSource(EventProgram $program, int $createdBy, ?string $overrideUrl = null): array
    {
        $url = trim((string) ($overrideUrl ?: $program->source_url));
        if ($url === '') {
            throw new RuntimeException('لا يوجد رابط Google Sheets محفوظ. اعمل Import مرة واحفظ الرابط، أو الصق الرابط مع التحديث.');
        }

        $tmpDir = storage_path('app/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }
        $tmp = $tmpDir.'/event_program_refresh_'.uniqid('', true).'.xlsx';

        try {
            $this->sheets->downloadXlsx($url, $tmp);
            $parsed = $this->parser->parseXlsx($tmp);
        } finally {
            @unlink($tmp);
        }

        $known = $this->knownPeopleMap($program);
        $parsed = $this->applyKnownPeople($parsed, $known);

        $preserveStatus = $program->status;
        $session = $this->importer->startSession(
            (int) $program->SeasonEventID,
            $parsed,
            $createdBy,
            'refresh',
            refreshMode: true,
        );

        // Soft resource questions: auto-resolve so mission edits stay one-click.
        $answers = [];
        $needsPersonReview = false;
        foreach ($session->questions_json ?? [] as $q) {
            $id = $q['id'] ?? null;
            if (! $id) {
                continue;
            }
            $code = $q['code'] ?? '';
            if ($code === 'person_unresolved') {
                $needsPersonReview = true;

                continue;
            }
            if ($code === 'resource_missing_url') {
                $answers[$id] = 'continue';
            } elseif ($code === 'resource_same_title_multi_day') {
                $answers[$id] = 'different';
            } else {
                $answers[$id] = ($q['options'][0]['value'] ?? 'continue');
            }
        }

        if ($needsPersonReview) {
            // Keep only person questions for the wizard; stash auto answers.
            $personQuestions = array_values(array_filter(
                $session->questions_json ?? [],
                fn ($q) => ($q['code'] ?? '') === 'person_unresolved'
            ));
            $session->questions_json = $personQuestions;
            $session->answers_json = $answers;
            $session->status = EventProgramImportSession::STATUS_PENDING_REVIEW;
            $session->event_program_id = $program->id;
            $session->save();

            $program->source_url = $url;
            $program->save();

            return [
                'program' => $program,
                'session' => $session,
                'needs_review' => true,
                'summary' => 'في قادة جدد محتاجين توضيح قبل اعتماد التحديث.',
            ];
        }

        if ($answers !== []) {
            $this->importer->answer($session, $answers);
        }

        $updated = $this->importer->commit($session->fresh(), [
            'preserve_status' => $preserveStatus,
            'source_url' => $url,
            'is_refresh' => true,
        ]);

        return [
            'program' => $updated,
            'session' => $session->fresh(),
            'needs_review' => false,
            'summary' => 'تم تحديث المهام والألعاب/المحاضرات من الشيت.',
        ];
    }

    /**
     * @return array<string, int> normalized name/code => person_id
     */
    public function knownPeopleMap(EventProgram $program): array
    {
        $map = [];
        $stored = $program->known_people_json ?? [];
        if (is_array($stored)) {
            foreach ($stored as $k => $v) {
                if (is_string($k) && is_numeric($v)) {
                    $map[$k] = (int) $v;
                }
            }
        }

        $program->loadMissing(['days.slots.assignments']);
        $personIds = [];
        foreach ($program->days as $day) {
            foreach ($day->slots as $slot) {
                foreach ($slot->assignments as $a) {
                    $personIds[(int) $a->person_id] = true;
                }
            }
        }

        if ($personIds !== []) {
            $people = DB::table('PersonInformation')
                ->whereIn('PersonID', array_keys($personIds))
                ->get(['PersonID', 'FirstName', 'SecondName', 'ThirdName', 'ShamandoraCode']);
            foreach ($people as $p) {
                $full = trim(implode(' ', array_filter([
                    $p->FirstName ?? null,
                    $p->SecondName ?? null,
                    $p->ThirdName ?? null,
                ])));
                if ($full !== '') {
                    $map[$this->key('name', $full)] = (int) $p->PersonID;
                    // also with common titles for sheet names like "كابتن باسم"
                    foreach (['كابتن', 'قائد', 'شفتان'] as $title) {
                        $map[$this->key('name', $title.' '.$full)] = (int) $p->PersonID;
                    }
                }
                if (! empty($p->ShamandoraCode)) {
                    $map[$this->key('code', (string) $p->ShamandoraCode)] = (int) $p->PersonID;
                }
            }
        }

        return $map;
    }

    /**
     * @param  array{meta: array, days: list<array>, resources: list<array>}  $parsed
     * @param  array<string, int>  $known
     * @return array{meta: array, days: list<array>, resources: list<array>}
     */
    public function applyKnownPeople(array $parsed, array $known): array
    {
        foreach ($parsed['days'] as &$day) {
            foreach ($day['leaders'] as &$leader) {
                if (! empty($leader['resolved_person_id']) || ! empty($leader['person_id'])) {
                    continue;
                }
                $code = trim((string) ($leader['shamandora_code'] ?? ''));
                $name = trim((string) ($leader['name'] ?? ''));
                if ($code !== '' && isset($known[$this->key('code', $code)])) {
                    $leader['resolved_person_id'] = $known[$this->key('code', $code)];
                    $leader['person_id'] = $leader['resolved_person_id'];

                    continue;
                }
                if ($name !== '' && isset($known[$this->key('name', $name)])) {
                    $leader['resolved_person_id'] = $known[$this->key('name', $name)];
                    $leader['person_id'] = $leader['resolved_person_id'];

                    continue;
                }
                // Try resolver + then known
                $resolved = $this->people->resolve(
                    $leader['person_id'] ?? null,
                    $leader['shamandora_code'] ?? null,
                    $leader['name'] ?? null,
                );
                if ($resolved['status'] === 'matched' && $resolved['person_id']) {
                    $leader['resolved_person_id'] = $resolved['person_id'];
                    $leader['person_id'] = $resolved['person_id'];
                }
            }
            unset($leader);
        }
        unset($day);

        return $parsed;
    }

    private function key(string $type, string $value): string
    {
        $value = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));

        return $type.':'.$value;
    }
}
