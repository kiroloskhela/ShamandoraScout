<?php

namespace App\Domain\EventProgram;

use App\Models\EventProgram;
use Illuminate\Support\Facades\DB;

final class EventProgramMessageComposer
{
    public function __construct(
        private readonly EventProgramQuery $query,
        private readonly CampEventTypeGate $gate,
        private readonly EventProgramResourceMatcher $resources,
    ) {}

    public function personDisplayName(int $personId): string
    {
        $p = DB::table('PersonInformation')->where('PersonID', $personId)->first();
        if (! $p) {
            return 'قائد';
        }

        return trim(implode(' ', array_filter([
            $p->FirstName ?? null,
            $p->SecondName ?? null,
            $p->ThirdName ?? null,
        ])));
    }

    public function personTitle(int $personId): string
    {
        $gender = DB::table('PersonInformation')->where('PersonID', $personId)->value('Gender');

        return strtolower((string) $gender) === 'f' || $gender === 'أنثى' || $gender === 'Female'
            ? 'شفتان'
            : 'قائد';
    }

    /**
     * @return array{day: int, text: string}|null
     */
    public function composeDayMessage(EventProgram $program, int $personId, int $dayNumber): ?array
    {
        $payload = $this->query->myProgramPayload((int) $program->SeasonEventID, $personId);
        if (! $payload) {
            // Allow composing from draft for WhatsApp preview/send admin path
            $program->loadMissing(['days.slots.assignments', 'resources']);
            $payload = $this->buildPayloadFromProgram($program, $personId);
        }

        if (! $payload) {
            return null;
        }

        $day = collect($payload['days'])->firstWhere('day_number', $dayNumber);
        if (! $day) {
            return null;
        }

        $meta = $this->gate->seasonEventMeta((int) $program->SeasonEventID);
        $eventName = $meta->EventName ?? $program->title;
        $name = $this->personDisplayName($personId);
        $title = $this->personTitle($personId);

        $introTpl = $program->intro_template ?: (string) config('event_program.default_intro');
        $outroTpl = $program->outro_template ?: (string) config('event_program.default_outro');

        $intro = strtr($introTpl, [
            '{title}' => $title,
            '{name}' => $name,
            '{day}' => (string) $dayNumber,
            '{event_name}' => (string) $eventName,
        ]);

        $lines = [];
        foreach ($day['slots'] as $slot) {
            $mission = trim((string) ($slot['mission_text'] ?? ''));
            $line = sprintf(
                'من %s لـ %s: %s',
                $slot['start_time'],
                $slot['end_time'],
                $mission !== '' ? $mission : ($slot['activity_label'] ?? '')
            );
            $lines[] = $line;

            foreach ($slot['resources'] ?? [] as $resource) {
                $kindLabel = ($resource['kind'] ?? '') === 'lecture' ? 'Lecture' : 'Game';
                $lines[] = $kindLabel.':';
                $lines[] = $kindLabel.' Name : '.($resource['title'] ?? '');
                $lines[] = 'Link : '.($resource['url'] ?? '');
            }
        }

        $text = rtrim($intro)."\n\n".implode("\n", $lines)."\n".ltrim($outroTpl);

        return ['day' => $dayNumber, 'text' => $text];
    }

    public function composeFullMessage(EventProgram $program, int $personId): ?string
    {
        $program->loadMissing(['days']);
        $parts = [];
        foreach ($program->days->sortBy('day_number') as $day) {
            $composed = $this->composeDayMessage($program, $personId, (int) $day->day_number);
            if ($composed) {
                $parts[] = $composed['text'];
            }
        }

        return $parts === [] ? null : implode("\n\n————\n\n", $parts);
    }

    /**
     * @return array{program: EventProgram, days: list<array<string, mixed>>}|null
     */
    private function buildPayloadFromProgram(EventProgram $program, int $personId): ?array
    {
        $days = [];
        foreach ($program->days as $day) {
            $slots = [];
            foreach ($day->slots as $slot) {
                $assignment = $slot->assignments->firstWhere('person_id', $personId);
                if (! $assignment) {
                    continue;
                }
                $resources = $this->resources->forAssignment(
                    $program,
                    $day,
                    $slot,
                    $assignment->mission_text
                );

                $slots[] = [
                    'start_time' => substr((string) $slot->start_time, 0, 5),
                    'end_time' => substr((string) $slot->end_time, 0, 5),
                    'activity_label' => $slot->activity_label,
                    'slot_kind' => $slot->slot_kind,
                    'mission_text' => $assignment->mission_text,
                    'resources' => $resources,
                ];
            }
            if ($slots !== []) {
                $days[] = [
                    'day_number' => $day->day_number,
                    'slots' => $slots,
                ];
            }
        }

        if ($days === []) {
            return null;
        }

        return ['program' => $program, 'days' => $days];
    }
}
