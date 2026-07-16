<?php

namespace App\Domain\SpecialCase;

use Illuminate\Support\Facades\DB;

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
}
