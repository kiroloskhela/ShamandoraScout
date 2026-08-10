<?php

namespace App\Domain\EventProgram;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use RuntimeException;

/**
 * Parses the guide-shaped workbook (xlsx) or a folder of CSVs into a structured DTO.
 * Also accepts the legacy camp sheet (Day 1..N + Games Links + Lectures Links)
 * without loading heavy tabs like "Sending Message".
 */
final class EventProgramParser
{
    /** Sheets we never load (can be tens of thousands of rows). */
    private const SKIP_SHEET_PATTERNS = [
        '/^sending/i',
        '/message/i',
        '/rotation/i',
        '/for leaders/i',
        '/^kashfy$/i',
    ];

    /**
     * @return array{
     *   meta: array<string, mixed>,
     *   days: list<array<string, mixed>>,
     *   resources: list<array<string, mixed>>
     * }
     */
    public function parseXlsx(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException('الملف غير موجود.');
        }

        $previous = ini_get('memory_limit');
        @ini_set('memory_limit', '512M');

        try {
            /** @var XlsxReader $reader */
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $allNames = $reader->listWorksheetNames($path);
            $loadNames = $this->selectSheetsToLoad($allNames);

            if ($loadNames === []) {
                throw new RuntimeException(
                    'لم نجد أوراق Day / Resources في الملف. استخدم قالب الإرشاد أو شيت فيه Day 1 و Day 2…'
                );
            }

            $reader->setLoadSheetsOnly($loadNames);
            $spreadsheet = $reader->load($path);

            $meta = ['season_event_id' => null, 'title' => '', 'day_count' => null, 'intro_template' => '', 'outro_template' => ''];
            if (in_array('Meta', $loadNames, true)) {
                $metaSheet = $spreadsheet->getSheetByName('Meta');
                if ($metaSheet) {
                    $meta = $this->parseMetaSheet($metaSheet);
                }
            }

            $days = [];
            $resources = [];

            foreach ($loadNames as $name) {
                $ws = $spreadsheet->getSheetByName($name);
                if (! $ws) {
                    continue;
                }
                if (preg_match('/^Day\s*(\d+)$/i', trim($name), $m)) {
                    $days[] = $this->parseDaySheet($ws, (int) $m[1]);
                } elseif (strcasecmp(trim($name), 'Resources') === 0) {
                    $resources = array_merge($resources, $this->parseResourcesSheet($ws));
                } elseif (strcasecmp(trim($name), 'Games Links') === 0) {
                    $resources = array_merge($resources, $this->parseLegacyGamesLinks($ws));
                } elseif (strcasecmp(trim($name), 'Lectures Links') === 0) {
                    $resources = array_merge($resources, $this->parseLegacyLecturesLinks($ws));
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            usort($days, fn ($a, $b) => $a['day_number'] <=> $b['day_number']);

            if ($days === []) {
                throw new RuntimeException('لا توجد بيانات أيام صالحة في الملف.');
            }

            if ($meta['title'] === '') {
                $meta['title'] = 'برنامج المعسكر';
            }
            if ($meta['day_count'] === null) {
                $meta['day_count'] = count($days);
            }

            return [
                'meta' => $meta,
                'days' => $days,
                'resources' => $resources,
            ];
        } finally {
            if (is_string($previous) && $previous !== '') {
                @ini_set('memory_limit', $previous);
            }
        }
    }

    /**
     * @param  list<string>  $allNames
     * @return list<string>
     */
    private function selectSheetsToLoad(array $allNames): array
    {
        $load = [];
        foreach ($allNames as $name) {
            $trim = trim($name);
            foreach (self::SKIP_SHEET_PATTERNS as $pattern) {
                if (preg_match($pattern, $trim)) {
                    continue 2;
                }
            }
            if (strcasecmp($trim, 'Meta') === 0
                || strcasecmp($trim, 'Resources') === 0
                || strcasecmp($trim, 'Games Links') === 0
                || strcasecmp($trim, 'Lectures Links') === 0
                || preg_match('/^Day\s*\d+$/i', $trim)
            ) {
                $load[] = $name;
            }
        }

        return $load;
    }

    /**
     * @param  array<string, string>  $csvPaths  map of sheet name => path
     * @return array{meta: array<string, mixed>, days: list<array<string, mixed>>, resources: list<array<string, mixed>>}
     */
    public function parseCsvPack(array $csvPaths): array
    {
        $meta = [];
        if (isset($csvPaths['Meta'])) {
            $rows = $this->readCsv($csvPaths['Meta']);
            $meta = $this->metaFromKeyValueRows($rows);
        }

        $days = [];
        $resources = [];
        foreach ($csvPaths as $name => $path) {
            if (preg_match('/^Day\s*(\d+)$/i', trim($name), $m)) {
                $rows = $this->readCsv($path);
                $days[] = $this->parseDayRows($rows, (int) $m[1]);
            } elseif (strcasecmp(trim($name), 'Resources') === 0) {
                $resources = $this->parseResourceRows($this->readCsv($path));
            }
        }

        usort($days, fn ($a, $b) => $a['day_number'] <=> $b['day_number']);

        return compact('meta', 'days', 'resources');
    }

    /** @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws */
    private function parseMetaSheet($ws): array
    {
        $rows = [];
        foreach ($ws->toArray(null, true, true, false) as $row) {
            $rows[] = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);
        }

        return $this->metaFromKeyValueRows($rows);
    }

    /** @param list<list<mixed>> $rows */
    private function metaFromKeyValueRows(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $key = strtolower(trim((string) ($row[0] ?? '')));
            $val = $row[1] ?? null;
            if ($key === '') {
                continue;
            }
            $map[$key] = is_string($val) ? trim($val) : $val;
        }

        return [
            'season_event_id' => isset($map['seasoneventid']) ? (int) $map['seasoneventid'] : (isset($map['season_event_id']) ? (int) $map['season_event_id'] : null),
            'title' => (string) ($map['title'] ?? $map['program title'] ?? ''),
            'day_count' => isset($map['day_count']) ? (int) $map['day_count'] : (isset($map['day count']) ? (int) $map['day count'] : null),
            'intro_template' => (string) ($map['intro_template'] ?? $map['intro'] ?? ''),
            'outro_template' => (string) ($map['outro_template'] ?? $map['outro'] ?? ''),
        ];
    }

    /** @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws */
    private function parseDaySheet($ws, int $dayNumber): array
    {
        $rows = $ws->toArray(null, true, true, false);

        return $this->parseDayRows($rows, $dayNumber);
    }

    /** @param list<list<mixed>> $rows */
    private function parseDayRows(array $rows, int $dayNumber): array
    {
        if (count($rows) < 2) {
            return [
                'day_number' => $dayNumber,
                'label' => 'يوم '.$dayNumber,
                'slots' => [],
                'leaders' => [],
            ];
        }

        // Drop trailing empty rows early (legacy sheets are tall but sparse).
        $lastUsed = count($rows) - 1;
        while ($lastUsed >= 2 && $this->rowIsEmpty($rows[$lastUsed])) {
            $lastUsed--;
        }
        $rows = array_slice($rows, 0, $lastUsed + 1);

        $timeRow = $rows[0];
        $labelRow = $rows[1];
        $legacy = $this->isLegacyDayHeader($labelRow);

        $slots = [];
        $colMap = [];
        $slotStartCol = 4;

        for ($c = $slotStartCol; $c < max(count($timeRow), count($labelRow)); $c++) {
            $timeHeader = trim((string) ($timeRow[$c] ?? ''));
            $activity = trim((string) ($labelRow[$c] ?? ''));
            if ($timeHeader === '' && $activity === '') {
                continue;
            }
            [$start, $end] = $this->parseTimeRange($timeHeader);
            $kind = $this->inferSlotKind($activity);
            $slotIndex = count($slots);
            $slots[] = [
                'start_time' => $start,
                'end_time' => $end,
                'activity_label' => $activity !== '' ? $activity : $timeHeader,
                'slot_kind' => $kind,
                'sort_order' => $slotIndex,
            ];
            $colMap[$c] = $slotIndex;
        }

        $leaders = [];
        for ($r = 2; $r < count($rows); $r++) {
            $row = $rows[$r];
            if ($legacy) {
                $name = trim((string) ($row[1] ?? ''));
                $personId = null;
                $code = null;
                $team = trim((string) ($row[2] ?? ''));
            } else {
                $name = trim((string) ($row[2] ?? ''));
                $personId = $this->intOrNull($row[0] ?? null);
                $code = trim((string) ($row[1] ?? ''));
                $team = trim((string) ($row[3] ?? ''));
            }

            if ($name === '' && ! $personId && ($code === null || $code === '')) {
                continue;
            }

            $missions = [];
            foreach ($colMap as $col => $slotIndex) {
                $missions[$slotIndex] = trim((string) ($row[$col] ?? ''));
            }
            $leaders[] = [
                'person_id' => $personId,
                'shamandora_code' => ($code !== null && $code !== '') ? $code : null,
                'name' => $name,
                'team_label' => $team !== '' ? $team : null,
                'missions' => $missions,
            ];
        }

        return [
            'day_number' => $dayNumber,
            'label' => 'يوم '.$dayNumber,
            'slots' => $slots,
            'leaders' => $leaders,
        ];
    }

    /** @param list<mixed> $labelRow */
    private function isLegacyDayHeader(array $labelRow): bool
    {
        $c0 = trim((string) ($labelRow[0] ?? ''));
        $c1 = trim((string) ($labelRow[1] ?? ''));
        $c2 = trim((string) ($labelRow[2] ?? ''));

        if ($c0 === 'مسلسل' || $c1 === 'القائد') {
            return true;
        }
        if (strcasecmp($c0, 'PersonID') === 0 || strcasecmp($c1, 'ShamandoraCode') === 0) {
            return false;
        }
        // If col2 looks like القائد and col0 is empty-ish serial header elsewhere
        if ($c2 === 'القائد') {
            return false;
        }

        return false;
    }

    /** @param list<mixed> $row */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if (trim((string) ($v ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    /** @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws */
    private function parseResourcesSheet($ws): array
    {
        return $this->parseResourceRows($ws->toArray(null, true, true, false));
    }

    /** @param list<list<mixed>> $rows */
    private function parseResourceRows(array $rows): array
    {
        $out = [];
        $start = 0;
        $header = array_map(fn ($v) => strtolower(trim((string) $v)), $rows[0] ?? []);
        if (in_array('kind', $header, true) || in_array('title', $header, true)) {
            $start = 1;
        }
        for ($i = $start; $i < count($rows); $i++) {
            $row = $rows[$i];
            $kind = strtolower(trim((string) ($row[0] ?? '')));
            $title = trim((string) ($row[3] ?? $row[2] ?? ''));
            if ($kind === '' && $title === '') {
                continue;
            }
            if (str_contains($kind, 'محاض') || $kind === 'lecture') {
                $kind = 'lecture';
            } else {
                $kind = 'game';
            }
            $out[] = [
                'kind' => $kind === 'lecture' ? 'lecture' : 'game',
                'day_number' => $this->intOrNull($row[1] ?? null),
                'slot_label' => trim((string) ($row[2] ?? '')) ?: null,
                'title' => $title !== '' ? $title : trim((string) ($row[3] ?? '')),
                'url' => trim((string) ($row[4] ?? '')) ?: null,
            ];
        }

        return $out;
    }

    /** @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws */
    private function parseLegacyGamesLinks($ws): array
    {
        $rows = $ws->toArray(null, true, true, false);
        $out = [];

        // Header groups often sit on one row: "Games - Day 1", "Games - Day 2 Slot 1", ...
        $groupDayByCol = [];
        foreach ($rows as $r => $row) {
            foreach ($row as $c => $val) {
                $text = trim((string) ($val ?? ''));
                if ($text !== '' && preg_match('/day\s*(\d+)/i', $text, $m) && ! str_starts_with(strtolower($text), 'http')) {
                    $groupDayByCol[(int) $c] = (int) $m[1];
                }
            }
            if ($r > 10) {
                break;
            }
        }

        foreach ($rows as $row) {
            $cols = array_values($row);
            for ($c = 0; $c < count($cols); $c++) {
                $cell = trim((string) ($cols[$c] ?? ''));
                if (! str_starts_with(strtolower($cell), 'http')) {
                    continue;
                }
                $title = trim((string) ($cols[$c + 1] ?? ''));
                $dayHint = trim((string) ($cols[$c + 2] ?? ''));
                $dayNumber = null;
                if (preg_match('/day\s*(\d+)/i', $dayHint, $m)) {
                    $dayNumber = (int) $m[1];
                } else {
                    // nearest group header to the left
                    $best = null;
                    foreach ($groupDayByCol as $gc => $gd) {
                        if ($gc <= $c && ($best === null || $gc > $best)) {
                            $best = $gc;
                            $dayNumber = $gd;
                        }
                    }
                }
                if ($title === '') {
                    $title = 'Game';
                }
                $out[] = [
                    'kind' => 'game',
                    'day_number' => $dayNumber,
                    'slot_label' => 'العاب',
                    'title' => $title,
                    'url' => $cell,
                ];
            }
        }

        return $out;
    }

    /** @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws */
    private function parseLegacyLecturesLinks($ws): array
    {
        $rows = $ws->toArray(null, true, true, false);
        $out = [];
        $currentSection = 'lecture';

        foreach ($rows as $row) {
            $vals = array_values(array_filter(array_map(
                fn ($v) => trim((string) ($v ?? '')),
                $row
            ), fn ($v) => $v !== ''));

            if ($vals === []) {
                continue;
            }

            // Section headers without URLs
            $joined = implode(' ', $vals);
            if (! str_contains(strtolower($joined), 'http') && (
                str_contains($joined, 'توصيل')
                || str_contains($joined, 'كشفي')
                || str_contains($joined, 'تقيم')
                || str_contains($joined, 'تقييم')
            )) {
                $currentSection = $joined;
                continue;
            }

            $url = null;
            $title = null;
            $dayNumber = null;
            foreach ($vals as $v) {
                if (str_starts_with(strtolower($v), 'http')) {
                    $url = $v;
                } elseif (preg_match('/^day\s*(\d+)$/i', $v, $m)) {
                    $dayNumber = (int) $m[1];
                } elseif ($title === null && ! preg_match('/^day\s*\d+$/i', $v)) {
                    $title = $v;
                }
            }

            if (! $url) {
                continue;
            }

            $out[] = [
                'kind' => 'lecture',
                'day_number' => $dayNumber,
                'slot_label' => $currentSection,
                'title' => $title ?: $currentSection,
                'url' => $url,
            ];
        }

        return $out;
    }

    /** @return array{0: string, 1: string} */
    private function parseTimeRange(string $header): array
    {
        $normalized = $this->easternToWesternDigits($header);
        if (preg_match('/(\d{1,2}:\d{2}).*?(\d{1,2}:\d{2})/u', $normalized, $m)) {
            return [$this->normalizeTime($m[1]), $this->normalizeTime($m[2])];
        }

        return ['00:00', '00:00'];
    }

    private function normalizeTime(string $t): string
    {
        [$h, $m] = array_pad(explode(':', $t), 2, '0');

        return sprintf('%02d:%02d', (int) $h, (int) $m);
    }

    private function easternToWesternDigits(string $s): string
    {
        $eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($eastern, $western, $s);
    }

    private function inferSlotKind(string $activity): string
    {
        $a = mb_strtolower($activity);
        if (str_contains($a, 'العاب') || str_contains($a, 'game') || str_contains($a, 'ألعاب')) {
            return 'games';
        }
        if (str_contains($a, 'lecture') || str_contains($a, 'محاضر') || str_contains($a, 'توصيل هدف') || str_contains($a, 'كشفي')) {
            return 'lecture';
        }
        if (str_contains($a, 'دوري') || str_contains($a, 'duty')) {
            return 'duty';
        }

        return 'general';
    }

    private function intOrNull(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            return (int) $v;
        }

        return null;
    }

    /** @return list<list<mixed>> */
    private function readCsv(string $path): array
    {
        $fh = fopen($path, 'r');
        if (! $fh) {
            throw new RuntimeException('تعذر قراءة CSV: '.$path);
        }
        $rows = [];
        while (($data = fgetcsv($fh)) !== false) {
            $rows[] = $data;
        }
        fclose($fh);

        return $rows;
    }
}
