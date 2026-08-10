<?php

namespace App\Domain\EventProgram;

use Illuminate\Support\Facades\DB;

final class PersonResolver
{
    /**
     * @return array{person_id: ?int, candidates: list<array{person_id: int, name: string, code: ?string}>, status: string}
     */
    public function resolve(?int $personId, ?string $shamandoraCode, ?string $name): array
    {
        if ($personId && DB::table('PersonInformation')->where('PersonID', $personId)->exists()) {
            return ['person_id' => $personId, 'candidates' => [], 'status' => 'matched'];
        }

        if ($shamandoraCode) {
            $byCode = DB::table('PersonInformation')
                ->where('ShamandoraCode', $shamandoraCode)
                ->first();
            if ($byCode) {
                return [
                    'person_id' => (int) $byCode->PersonID,
                    'candidates' => [],
                    'status' => 'matched',
                ];
            }
        }

        $name = trim((string) $name);
        if ($name === '') {
            return ['person_id' => null, 'candidates' => [], 'status' => 'missing'];
        }

        $name = $this->normalizeLeaderName($name);
        $parts = preg_split('/\s+/u', $name) ?: [];
        $candidatesQuery = DB::table('PersonInformation')
            ->select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'ShamandoraCode');

        if (isset($parts[0]) && $parts[0] !== '') {
            $candidatesQuery->where('FirstName', 'like', $parts[0].'%');
        }

        $pool = $candidatesQuery->limit(50)->get();
        $exact = $pool->filter(function ($p) use ($name) {
            $full = trim(implode(' ', array_filter([
                $p->FirstName ?? null,
                $p->SecondName ?? null,
                $p->ThirdName ?? null,
            ])));

            return $full === $name || mb_strtolower($full) === mb_strtolower($name);
        })->values();

        if ($exact->count() === 1) {
            return [
                'person_id' => (int) $exact->first()->PersonID,
                'candidates' => [],
                'status' => 'matched',
            ];
        }

        if ($exact->count() > 1) {
            return [
                'person_id' => null,
                'candidates' => $exact->take(5)->map(fn ($p) => $this->candidate($p))->all(),
                'status' => 'ambiguous',
            ];
        }

        $q = DB::table('PersonInformation')
            ->select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'ShamandoraCode');

        if (isset($parts[0]) && $parts[0] !== '') {
            $q->where('FirstName', 'like', $parts[0].'%');
        }
        if (isset($parts[1]) && $parts[1] !== '') {
            $q->where('SecondName', 'like', $parts[1].'%');
        }

        $fuzzy = $q->limit(5)->get();
        if ($fuzzy->isEmpty() && isset($parts[0])) {
            $fuzzy = DB::table('PersonInformation')
                ->select('PersonID', 'FirstName', 'SecondName', 'ThirdName', 'ShamandoraCode')
                ->where('FirstName', 'like', '%'.$parts[0].'%')
                ->limit(5)
                ->get();
        }

        return [
            'person_id' => null,
            'candidates' => $fuzzy->map(fn ($p) => $this->candidate($p))->all(),
            'status' => $fuzzy->isEmpty() ? 'unmatched' : 'ambiguous',
        ];
    }

    private function normalizeLeaderName(string $name): string
    {
        $name = preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);
        $titles = ['كابتن', 'قائد', 'شفتان', 'شيفتان', 'الأب', 'ابونا', 'أبونا', 'Captain', 'captain'];
        foreach ($titles as $title) {
            if (str_starts_with($name, $title.' ')) {
                $name = trim(mb_substr($name, mb_strlen($title)));
            }
        }

        return $name;
    }

    private function candidate(object $p): array
    {
        return [
            'person_id' => (int) $p->PersonID,
            'name' => trim(implode(' ', array_filter([
                $p->FirstName ?? null,
                $p->SecondName ?? null,
                $p->ThirdName ?? null,
            ]))),
            'code' => $p->ShamandoraCode ?? null,
        ];
    }
}
