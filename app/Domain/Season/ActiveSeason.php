<?php

namespace App\Domain\Season;

use Illuminate\Support\Facades\DB;
use stdClass;

class ActiveSeason
{
    public function id(): ?int
    {
        $id = DB::table('Season')->where('IsActive', 1)->value('SeasonID');

        if ($id) {
            return (int) $id;
        }

        $fallback = DB::table('Season')->orderByDesc('SeasonYear')->value('SeasonID');

        return $fallback ? (int) $fallback : null;
    }

    public function get(): ?stdClass
    {
        $season = DB::table('Season')->where('IsActive', 1)->first();

        if ($season) {
            return $season;
        }

        return DB::table('Season')->orderByDesc('SeasonYear')->first();
    }

    public function activate(int $seasonId): void
    {
        DB::transaction(function () use ($seasonId) {
            $exists = DB::table('Season')->where('SeasonID', $seasonId)->exists();
            if (! $exists) {
                abort(404);
            }

            DB::table('Season')->update(['IsActive' => 0]);
            DB::table('Season')->where('SeasonID', $seasonId)->update(['IsActive' => 1]);
        });
    }
}
