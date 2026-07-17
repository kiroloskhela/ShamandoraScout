<?php

namespace App\Domain\SpecialCase;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PersonSpecialCaseService
{
    public function create(int $personId, int $serventId, ?string $note): int
    {
        return (int) DB::table('PersonSpecialCase')->insertGetId([
            'PersonID' => $personId,
            'ServentID' => $serventId,
            'CaseDate' => now(),
            'Note' => $note,
        ]);
    }

    public function updateNote(int $specialCaseId, ?string $note): void
    {
        $updated = DB::table('PersonSpecialCase')
            ->where('SpecialCaseID', $specialCaseId)
            ->update([
                'Note' => $note,
            ]);

        if ($updated === 0) {
            throw new RuntimeException('Special case not found');
        }
    }

    public function delete(int $specialCaseId): void
    {
        $deleted = DB::table('PersonSpecialCase')
            ->where('SpecialCaseID', $specialCaseId)
            ->delete();

        if ($deleted === 0) {
            throw new RuntimeException('Special case not found');
        }
    }
}
