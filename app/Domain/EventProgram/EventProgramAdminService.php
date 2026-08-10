<?php

namespace App\Domain\EventProgram;

use App\Models\EventProgram;
use App\Models\EventProgramAssignment;
use App\Models\EventProgramDay;
use App\Models\EventProgramResource;
use App\Models\EventProgramSlot;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EventProgramAdminService
{
    public function __construct(
        private readonly CampEventTypeGate $gate,
    ) {}

    public function createOrGet(int $seasonEventId, ?string $title = null): EventProgram
    {
        if (! $this->gate->isCampSeasonEvent($seasonEventId)) {
            throw new RuntimeException('نوع الحدث غير مدعوم لبرنامج القادة.');
        }

        $meta = $this->gate->seasonEventMeta($seasonEventId);
        $program = EventProgram::query()->firstOrNew(['SeasonEventID' => $seasonEventId]);
        if (! $program->exists) {
            $program->title = $title ?: (string) ($meta->EventName ?? 'برنامج المعسكر');
            $program->status = EventProgram::STATUS_DRAFT;
            $program->intro_template = (string) config('event_program.default_intro');
            $program->outro_template = (string) config('event_program.default_outro');
            $program->save();
        }

        return $program->fresh(['days.slots.assignments', 'resources']);
    }

    public function publish(EventProgram $program): EventProgram
    {
        $has = EventProgramAssignment::query()
            ->whereHas('slot.day', fn ($q) => $q->where('event_program_id', $program->id))
            ->exists();
        if (! $has) {
            throw new RuntimeException('لا يمكن النشر بدون مهام للقادة.');
        }

        $program->status = EventProgram::STATUS_PUBLISHED;
        $program->save();

        return $program;
    }

    public function unpublish(EventProgram $program): EventProgram
    {
        $program->status = EventProgram::STATUS_DRAFT;
        $program->save();

        return $program;
    }

    /**
     * @param  array{day_number: int, label?: string, date?: ?string}  $data
     */
    public function addDay(EventProgram $program, array $data): EventProgramDay
    {
        return EventProgramDay::create([
            'event_program_id' => $program->id,
            'day_number' => (int) $data['day_number'],
            'label' => $data['label'] ?? ('يوم '.$data['day_number']),
            'date' => $data['date'] ?? null,
        ]);
    }

    /**
     * @param  array{start_time: string, end_time: string, activity_label: string, slot_kind?: string, sort_order?: int}  $data
     */
    public function addSlot(EventProgramDay $day, array $data): EventProgramSlot
    {
        return EventProgramSlot::create([
            'event_program_day_id' => $day->id,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'activity_label' => $data['activity_label'],
            'slot_kind' => $data['slot_kind'] ?? 'general',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /**
     * @param  array{person_id: int, mission_text?: ?string, team_label?: ?string}  $data
     */
    public function upsertAssignment(EventProgramSlot $slot, array $data): EventProgramAssignment
    {
        return EventProgramAssignment::updateOrCreate(
            [
                'event_program_slot_id' => $slot->id,
                'person_id' => (int) $data['person_id'],
            ],
            [
                'mission_text' => $data['mission_text'] ?? null,
                'team_label' => $data['team_label'] ?? null,
            ]
        );
    }

    /**
     * @param  array{kind: string, title: string, url?: ?string, day_id?: ?int, slot_label?: ?string}  $data
     */
    public function addResource(EventProgram $program, array $data): EventProgramResource
    {
        return EventProgramResource::create([
            'event_program_id' => $program->id,
            'event_program_day_id' => $data['day_id'] ?? null,
            'kind' => $data['kind'] === 'lecture' ? 'lecture' : 'game',
            'title' => $data['title'],
            'url' => $data['url'] ?? null,
            'slot_label' => $data['slot_label'] ?? null,
        ]);
    }

    /**
     * Seed leader person IDs from bookings when available.
     *
     * @return list<int>
     */
    public function bookedPersonIds(int $seasonEventId): array
    {
        if (! DB::getSchemaBuilder()->hasTable('SeasonEventParticipantFinance')) {
            return [];
        }

        return DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', $seasonEventId)
            ->whereNotNull('PersonID')
            ->distinct()
            ->pluck('PersonID')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
