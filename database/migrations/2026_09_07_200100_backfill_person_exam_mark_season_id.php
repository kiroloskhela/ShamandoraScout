<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PersonExamMark')
            || ! Schema::hasColumn('PersonExamMark', 'SeasonID')
            || ! Schema::hasTable('Season')) {
            return;
        }

        $seasonsByYear = DB::table('Season')->pluck('SeasonID', 'SeasonYear');
        $fallback = DB::table('Season')->where('IsActive', 1)->value('SeasonID')
            ?? DB::table('Season')->orderByDesc('SeasonYear')->value('SeasonID');

        if ($seasonsByYear->isEmpty() && ! $fallback) {
            return;
        }

        $marks = DB::table('PersonExamMark')
            ->whereNull('SeasonID')
            ->get(['ExamMarkID', 'ExamDate']);

        foreach ($marks as $mark) {
            $year = (int) substr((string) $mark->ExamDate, 0, 4);
            $seasonId = $seasonsByYear[$year] ?? $fallback;
            if (! $seasonId) {
                continue;
            }

            DB::table('PersonExamMark')
                ->where('ExamMarkID', $mark->ExamMarkID)
                ->update(['SeasonID' => $seasonId]);
        }
    }

    public function down(): void
    {
        // Keep assigned seasons; this was a one-way data backfill.
    }
};
