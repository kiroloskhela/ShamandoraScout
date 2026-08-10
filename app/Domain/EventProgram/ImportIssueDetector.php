<?php

namespace App\Domain\EventProgram;

final class ImportIssueDetector
{
    public function __construct(
        private readonly PersonResolver $people,
    ) {}

    /**
     * @param  array{meta: array, days: list<array>, resources: list<array>}  $parsed
     * @return array{hard: list<array>, soft: list<array>}
     */
    public function detect(array $parsed): array
    {
        $hard = [];
        $soft = [];

        if (($parsed['days'] ?? []) === []) {
            $hard[] = [
                'code' => 'no_days',
                'message' => 'لا توجد أوراق Day في الملف.',
            ];
        }

        foreach ($parsed['days'] as $day) {
            $dayNumber = (int) $day['day_number'];
            if (($day['slots'] ?? []) === []) {
                $hard[] = [
                    'code' => 'no_slots',
                    'message' => "اليوم {$dayNumber} بدون فقرات زمنية.",
                    'day_number' => $dayNumber,
                ];
            }

            foreach ($day['slots'] as $i => $slot) {
                if (($slot['start_time'] ?? '00:00') === '00:00' && ($slot['end_time'] ?? '00:00') === '00:00') {
                    $soft[] = [
                        'code' => 'bad_time',
                        'message' => "وقت غير واضح في يوم {$dayNumber} فقرة ".($i + 1),
                        'day_number' => $dayNumber,
                        'slot_index' => $i,
                    ];
                }
            }

            $seenPeople = [];
            foreach ($day['leaders'] as $li => $leader) {
                if (! empty($leader['resolved_person_id'])) {
                    continue;
                }

                $resolved = $this->people->resolve(
                    $leader['person_id'] ?? null,
                    $leader['shamandora_code'] ?? null,
                    $leader['name'] ?? null,
                );

                if ($resolved['status'] === 'matched') {
                    continue;
                }

                $key = mb_strtolower(trim((string) (
                    ($leader['shamandora_code'] ?? '') !== ''
                        ? 'code:'.$leader['shamandora_code']
                        : 'name:'.($leader['name'] ?? '')
                )));
                if ($key === 'code:' || $key === 'name:' || isset($seenPeople[$key])) {
                    continue;
                }
                $seenPeople[$key] = true;

                $soft[] = [
                    'code' => 'person_unresolved',
                    'message' => 'تعذر مطابقة القائد: '.($leader['name'] ?? 'بدون اسم'),
                    'day_number' => $dayNumber,
                    'leader_index' => $li,
                    'name' => $leader['name'] ?? null,
                    'shamandora_code' => $leader['shamandora_code'] ?? null,
                    'person_id_input' => $leader['person_id'] ?? null,
                    'candidates' => $resolved['candidates'],
                    'status' => $resolved['status'],
                ];
            }
        }

        // Duplicate game titles across days with different URLs
        $byTitle = [];
        foreach ($parsed['resources'] as $ri => $res) {
            $title = mb_strtolower(trim((string) ($res['title'] ?? '')));
            if ($title === '') {
                $soft[] = [
                    'code' => 'resource_missing_title',
                    'message' => 'مورد بدون عنوان.',
                    'resource_index' => $ri,
                ];

                continue;
            }
            if (empty($res['url'])) {
                $soft[] = [
                    'code' => 'resource_missing_url',
                    'message' => 'مورد بدون لينك: '.($res['title'] ?? ''),
                    'resource_index' => $ri,
                    'title' => $res['title'] ?? null,
                    'kind' => $res['kind'] ?? null,
                    'day_number' => $res['day_number'] ?? null,
                ];
            }
            $byTitle[$title][] = ['index' => $ri, 'resource' => $res];
        }

        foreach ($byTitle as $title => $items) {
            if (count($items) < 2) {
                continue;
            }
            $urls = array_unique(array_map(fn ($i) => (string) ($i['resource']['url'] ?? ''), $items));
            $days = array_unique(array_filter(array_map(fn ($i) => $i['resource']['day_number'] ?? null, $items)));
            if (count($urls) > 1 || count($days) > 1) {
                $soft[] = [
                    'code' => 'resource_same_title_multi_day',
                    'message' => "العنوان \"{$items[0]['resource']['title']}\" يظهر أكثر من مرة — نفس المورد ولا مختلف؟",
                    'title' => $items[0]['resource']['title'],
                    'occurrences' => array_map(fn ($i) => [
                        'resource_index' => $i['index'],
                        'day_number' => $i['resource']['day_number'] ?? null,
                        'url' => $i['resource']['url'] ?? null,
                    ], $items),
                ];
            }
        }

        return ['hard' => $hard, 'soft' => $soft];
    }
}
