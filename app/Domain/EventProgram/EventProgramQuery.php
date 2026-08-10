<?php

namespace App\Domain\EventProgram;

use App\Models\EventProgram;
use App\Models\EventProgramAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class EventProgramQuery
{
    public function __construct(
        private readonly EventProgramResourceMatcher $resources,
    ) {}

    public function findPublishedForSeasonEvent(int $seasonEventId): ?EventProgram
    {
        return EventProgram::query()
            ->where('SeasonEventID', $seasonEventId)
            ->where('status', EventProgram::STATUS_PUBLISHED)
            ->with(['days.slots.assignments', 'resources'])
            ->first();
    }

    public function findForSeasonEvent(int $seasonEventId): ?EventProgram
    {
        return EventProgram::query()
            ->where('SeasonEventID', $seasonEventId)
            ->with(['days.slots.assignments', 'resources'])
            ->first();
    }

    /**
     * Programs visible to a person (published + has at least one assignment).
     *
     * @return Collection<int, object>
     */
    public function listForPerson(int $personId): Collection
    {
        return DB::table('event_program_assignments as a')
            ->join('event_program_slots as s', 'a.event_program_slot_id', '=', 's.id')
            ->join('event_program_days as d', 's.event_program_day_id', '=', 'd.id')
            ->join('event_programs as p', 'd.event_program_id', '=', 'p.id')
            ->join('SeasonEvent as se', 'p.SeasonEventID', '=', 'se.SeasonEventID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->where('a.person_id', $personId)
            ->where('p.status', EventProgram::STATUS_PUBLISHED)
            ->groupBy('p.id', 'p.SeasonEventID', 'p.title', 'e.EventName', 'e.EventStartDate', 'e.EventEndDate')
            ->orderByDesc('e.EventStartDate')
            ->select([
                'p.id as program_id',
                'p.SeasonEventID',
                'p.title',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
            ])
            ->get();
    }

    /**
     * @return array{
     *   program: EventProgram,
     *   days: list<array<string, mixed>>
     * }|null
     */
    public function myProgramPayload(int $seasonEventId, int $personId): ?array
    {
        $program = $this->findPublishedForSeasonEvent($seasonEventId);
        if (! $program) {
            return null;
        }

        $hasAssignment = EventProgramAssignment::query()
            ->where('person_id', $personId)
            ->whereHas('slot.day', fn ($q) => $q->where('event_program_id', $program->id))
            ->exists();

        if (! $hasAssignment) {
            return null;
        }

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
                    'id' => $slot->id,
                    'start_time' => substr((string) $slot->start_time, 0, 5),
                    'end_time' => substr((string) $slot->end_time, 0, 5),
                    'activity_label' => $slot->activity_label,
                    'slot_kind' => $slot->slot_kind,
                    'mission_text' => $assignment->mission_text,
                    'team_label' => $assignment->team_label,
                    'resources' => $resources,
                ];
            }

            if ($slots === []) {
                continue;
            }

            $days[] = [
                'id' => $day->id,
                'day_number' => $day->day_number,
                'label' => $day->label ?: ('يوم '.$day->day_number),
                'date' => optional($day->date)?->format('Y-m-d'),
                'slots' => $slots,
            ];
        }

        return [
            'program' => $program,
            'days' => $days,
        ];
    }
}
