<?php

namespace App\Domain\EventProgram;

use Illuminate\Support\Facades\DB;

final class CampEventTypeGate
{
    /** @return list<string> */
    public static function allowedTypes(): array
    {
        return array_values(config('event_program.camp_event_types', []));
    }

    public function isCampSeasonEvent(int $seasonEventId): bool
    {
        $type = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->value('et.EventTypeName');

        return is_string($type) && in_array($type, self::allowedTypes(), true);
    }

    /**
     * @return object|null
     */
    public function seasonEventMeta(int $seasonEventId): ?object
    {
        return DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->select([
                'se.SeasonEventID',
                'se.SeasonID',
                'se.EventID',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
                'et.EventTypeName',
                's.SeasonName',
                's.SeasonYear',
            ])
            ->first();
    }
}
