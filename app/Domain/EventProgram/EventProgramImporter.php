<?php

namespace App\Domain\EventProgram;

use App\Models\EventProgram;
use App\Models\EventProgramAssignment;
use App\Models\EventProgramDay;
use App\Models\EventProgramImportSession;
use App\Models\EventProgramResource;
use App\Models\EventProgramSlot;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EventProgramImporter
{
    public function __construct(
        private readonly EventProgramParser $parser,
        private readonly ImportIssueDetector $issues,
        private readonly GeminiImportAssistant $assistant,
        private readonly CampEventTypeGate $gate,
        private readonly PersonResolver $people,
    ) {}

    public function startFromXlsx(int $seasonEventId, string $path, int $createdBy, string $source = 'upload'): EventProgramImportSession
    {
        $this->assertCamp($seasonEventId);
        $parsed = $this->parser->parseXlsx($path);

        return $this->startSession($seasonEventId, $parsed, $createdBy, $source);
    }

    /**
     * @param  array<string, string>  $csvPaths
     */
    public function startFromCsvPack(int $seasonEventId, array $csvPaths, int $createdBy, string $source = 'upload'): EventProgramImportSession
    {
        $this->assertCamp($seasonEventId);
        $parsed = $this->parser->parseCsvPack($csvPaths);

        return $this->startSession($seasonEventId, $parsed, $createdBy, $source);
    }

    /**
     * @param  array{meta: array, days: list<array>, resources: list<array>}  $parsed
     */
    public function startSession(
        int $seasonEventId,
        array $parsed,
        int $createdBy,
        string $source,
        bool $refreshMode = false,
    ): EventProgramImportSession {
        $parsed['meta']['season_event_id'] = $seasonEventId;
        $detected = $this->issues->detect($parsed);

        if ($detected['hard'] !== []) {
            throw new RuntimeException(
                'أخطاء تمنع الاستيراد: '.implode(' | ', array_map(fn ($h) => $h['message'], $detected['hard']))
            );
        }

        // Refresh: ignore soft resource noise; keep only unresolved people.
        if ($refreshMode) {
            $detected['soft'] = array_values(array_filter(
                $detected['soft'] ?? [],
                fn ($i) => ($i['code'] ?? '') === 'person_unresolved'
            ));
        }

        $meta = $this->gate->seasonEventMeta($seasonEventId);
        $questions = $this->assistant->buildQuestions($detected, [
            'season_event_id' => $seasonEventId,
            'event_name' => $meta->EventName ?? null,
            'event_type' => $meta->EventTypeName ?? null,
            'refresh' => $refreshMode,
        ]);

        $status = $questions === []
            ? EventProgramImportSession::STATUS_READY
            : EventProgramImportSession::STATUS_PENDING_REVIEW;

        return EventProgramImportSession::create([
            'SeasonEventID' => $seasonEventId,
            'created_by' => $createdBy,
            'status' => $status,
            'source' => $source,
            'parsed_json' => $parsed,
            'issues_json' => $detected,
            'questions_json' => $questions,
            'answers_json' => [],
        ]);
    }

    /**
     * @param  array<string, mixed>  $answers
     */
    public function answer(EventProgramImportSession $session, array $answers): EventProgramImportSession
    {
        $merged = array_merge($session->answers_json ?? [], $answers);
        $session->answers_json = $merged;
        $session->status = EventProgramImportSession::STATUS_READY;
        $session->save();

        return $session;
    }

    /**
     * @param  array{preserve_status?: string|null, source_url?: ?string, is_refresh?: bool}  $options
     */
    public function commit(EventProgramImportSession $session, array $options = []): EventProgram
    {
        if (! in_array($session->status, [
            EventProgramImportSession::STATUS_READY,
            EventProgramImportSession::STATUS_PENDING_REVIEW,
        ], true)) {
            throw new RuntimeException('جلسة الاستيراد غير جاهزة.');
        }

        $parsed = $session->parsed_json ?? [];
        $answers = $session->answers_json ?? [];
        $parsed = $this->applyAnswers($parsed, $answers, $session->questions_json ?? []);

        $seasonEventId = (int) $session->SeasonEventID;
        $meta = $this->gate->seasonEventMeta($seasonEventId);
        $title = trim((string) ($parsed['meta']['title'] ?? '')) ?: (string) ($meta->EventName ?? 'برنامج القائد');
        $preserveStatus = $options['preserve_status'] ?? null;
        $sourceUrl = $options['source_url'] ?? null;
        $isRefresh = (bool) ($options['is_refresh'] ?? false);

        return DB::transaction(function () use ($session, $parsed, $seasonEventId, $title, $preserveStatus, $sourceUrl, $isRefresh) {
            $program = EventProgram::query()->firstOrNew(['SeasonEventID' => $seasonEventId]);
            $status = $preserveStatus ?: EventProgram::STATUS_DRAFT;
            if (! in_array($status, [EventProgram::STATUS_DRAFT, EventProgram::STATUS_PUBLISHED], true)) {
                $status = EventProgram::STATUS_DRAFT;
            }

            $fill = [
                'title' => $title !== '' ? $title : ($program->title ?: 'برنامج القائد'),
                'status' => $status,
            ];
            // Don't wipe intro/outro on refresh if sheet meta empty
            $intro = trim((string) ($parsed['meta']['intro_template'] ?? ''));
            $outro = trim((string) ($parsed['meta']['outro_template'] ?? ''));
            if ($intro !== '') {
                $fill['intro_template'] = $intro;
            } elseif (! $program->exists) {
                $fill['intro_template'] = (string) config('event_program.default_intro');
            }
            if ($outro !== '') {
                $fill['outro_template'] = $outro;
            } elseif (! $program->exists) {
                $fill['outro_template'] = (string) config('event_program.default_outro');
            }
            if (is_string($sourceUrl) && $sourceUrl !== '') {
                $fill['source_url'] = $sourceUrl;
            }
            if ($isRefresh) {
                $fill['last_refreshed_at'] = now();
            }

            $program->fill($fill);
            $program->save();

            foreach ($program->days as $day) {
                foreach ($day->slots as $slot) {
                    $slot->assignments()->delete();
                    $slot->delete();
                }
                $day->delete();
            }
            $program->resources()->delete();

            $dayModels = [];
            $knownPeople = $program->known_people_json ?? [];
            if (! is_array($knownPeople)) {
                $knownPeople = [];
            }

            foreach ($parsed['days'] as $dayData) {
                $day = EventProgramDay::create([
                    'event_program_id' => $program->id,
                    'day_number' => (int) $dayData['day_number'],
                    'label' => $dayData['label'] ?? ('يوم '.$dayData['day_number']),
                ]);
                $dayModels[(int) $dayData['day_number']] = $day;

                $slotModels = [];
                foreach ($dayData['slots'] as $si => $slotData) {
                    $slot = EventProgramSlot::create([
                        'event_program_day_id' => $day->id,
                        'start_time' => $slotData['start_time'],
                        'end_time' => $slotData['end_time'],
                        'activity_label' => $slotData['activity_label'],
                        'slot_kind' => $slotData['slot_kind'] ?? 'general',
                        'sort_order' => $slotData['sort_order'] ?? $si,
                    ]);
                    $slotModels[$si] = $slot;
                }

                foreach ($dayData['leaders'] as $leader) {
                    $personId = $leader['resolved_person_id']
                        ?? $this->people->resolve(
                            $leader['person_id'] ?? null,
                            $leader['shamandora_code'] ?? null,
                            $leader['name'] ?? null,
                        )['person_id'];

                    if (! $personId) {
                        continue;
                    }

                    $name = trim((string) ($leader['name'] ?? ''));
                    if ($name !== '') {
                        $knownPeople['name:'.mb_strtolower($name)] = (int) $personId;
                    }
                    $code = trim((string) ($leader['shamandora_code'] ?? ''));
                    if ($code !== '') {
                        $knownPeople['code:'.mb_strtolower($code)] = (int) $personId;
                    }

                    foreach ($leader['missions'] as $slotIndex => $mission) {
                        if (! isset($slotModels[$slotIndex])) {
                            continue;
                        }
                        EventProgramAssignment::updateOrCreate(
                            [
                                'event_program_slot_id' => $slotModels[$slotIndex]->id,
                                'person_id' => $personId,
                            ],
                            [
                                'mission_text' => $mission !== '' ? $mission : null,
                                'team_label' => $leader['team_label'] ?? null,
                            ]
                        );
                    }
                }
            }

            foreach ($parsed['resources'] as $res) {
                if (! empty($res['_skip'])) {
                    continue;
                }
                $dayId = null;
                if (! empty($res['day_number']) && isset($dayModels[(int) $res['day_number']])) {
                    $dayId = $dayModels[(int) $res['day_number']]->id;
                }
                EventProgramResource::create([
                    'event_program_id' => $program->id,
                    'event_program_day_id' => $dayId,
                    'kind' => $res['kind'] === 'lecture' ? 'lecture' : 'game',
                    'title' => $res['title'],
                    'url' => $res['url'] ?? null,
                    'slot_label' => $res['slot_label'] ?? null,
                ]);
            }

            $program->known_people_json = $knownPeople;
            $program->save();

            $session->event_program_id = $program->id;
            $session->status = EventProgramImportSession::STATUS_COMMITTED;
            $session->save();

            return $program->fresh(['days.slots.assignments', 'resources']);
        });
    }

    /**
     * @param  array{meta: array, days: list<array>, resources: list<array>}  $parsed
     * @param  array<string, mixed>  $answers
     * @param  list<array>  $questions
     * @return array{meta: array, days: list<array>, resources: list<array>}
     */
    private function applyAnswers(array $parsed, array $answers, array $questions): array
    {
        foreach ($questions as $q) {
            $id = $q['id'] ?? null;
            if (! $id || ! array_key_exists($id, $answers)) {
                continue;
            }
            $answer = $answers[$id];
            $code = $q['code'] ?? '';
            $meta = $q['meta'] ?? [];

            if ($code === 'person_unresolved') {
                $targetName = mb_strtolower(trim((string) ($meta['name'] ?? '')));
                $targetCode = trim((string) ($meta['shamandora_code'] ?? ''));
                foreach ($parsed['days'] as &$day) {
                    foreach ($day['leaders'] as $idx => &$leader) {
                        $same = false;
                        if ($targetCode !== '' && ($leader['shamandora_code'] ?? null) === $targetCode) {
                            $same = true;
                        } elseif ($targetName !== '' && mb_strtolower(trim((string) ($leader['name'] ?? ''))) === $targetName) {
                            $same = true;
                        } elseif (
                            (int) ($meta['day_number'] ?? -1) === (int) $day['day_number']
                            && (int) ($meta['leader_index'] ?? -1) === $idx
                        ) {
                            $same = true;
                        }
                        if (! $same) {
                            continue;
                        }
                        if ($answer === 'skip') {
                            $leader['_skip'] = true;
                        } elseif (is_numeric($answer)) {
                            $leader['resolved_person_id'] = (int) $answer;
                            $leader['person_id'] = (int) $answer;
                        }
                    }
                    unset($leader);
                    $day['leaders'] = array_values(array_filter(
                        $day['leaders'],
                        fn ($l) => empty($l['_skip'])
                    ));
                }
                unset($day);
            }

            if ($code === 'resource_missing_url' && $answer === 'skip') {
                $idx = $meta['resource_index'] ?? null;
                if ($idx !== null && isset($parsed['resources'][$idx])) {
                    $parsed['resources'][$idx]['_skip'] = true;
                }
            }

            if ($code === 'resource_same_title_multi_day' && $answer === 'same') {
                $occurrences = $meta['occurrences'] ?? [];
                $firstUrl = null;
                foreach ($occurrences as $occ) {
                    if (! empty($occ['url'])) {
                        $firstUrl = $occ['url'];
                        break;
                    }
                }
                if ($firstUrl) {
                    foreach ($occurrences as $occ) {
                        $ri = $occ['resource_index'] ?? null;
                        if ($ri !== null && isset($parsed['resources'][$ri])) {
                            $parsed['resources'][$ri]['url'] = $firstUrl;
                        }
                    }
                }
            }
        }

        return $parsed;
    }

    private function assertCamp(int $seasonEventId): void
    {
        if (! $this->gate->isCampSeasonEvent($seasonEventId)) {
            throw new RuntimeException('هذا الحدث ليس من أنواع المعسكر المدعومة.');
        }
    }
}
