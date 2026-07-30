<?php

namespace App\Domain\EventProgram;

use App\Models\EventProgram;
use App\Models\EventProgramDay;
use App\Models\EventProgramSlot;
use Illuminate\Support\Collection;

/**
 * Decide which game/lecture links a leader should see for one slot.
 *
 * Games: only when mission mentions a specific game title → that game only.
 * Lectures: specific title in mission → that lecture; generic lecture duty → all lectures for the day.
 */
final class EventProgramResourceMatcher
{
    /**
     * @return list<array{kind: string, title: string, url: ?string}>
     */
    public function forAssignment(
        EventProgram $program,
        EventProgramDay $day,
        EventProgramSlot $slot,
        ?string $missionText,
    ): array {
        $mission = trim((string) $missionText);
        if ($mission === '') {
            return [];
        }

        $kind = $this->inferResourceKindFromMission($mission, (string) $slot->slot_kind);
        if ($kind === null) {
            return [];
        }

        $candidates = $program->resources
            ->filter(function ($r) use ($day, $slot, $kind) {
                if ($r->kind !== $kind) {
                    return false;
                }
                if ($r->event_program_slot_id && (int) $r->event_program_slot_id === (int) $slot->id) {
                    return true;
                }
                if ($r->event_program_day_id && (int) $r->event_program_day_id === (int) $day->id) {
                    return true;
                }

                // Untied catalog rows (legacy import) — still day-scoped by caller filters later.
                return $r->event_program_day_id === null && $r->event_program_slot_id === null;
            })
            ->values();

        if ($candidates->isEmpty()) {
            return [];
        }

        $specific = $this->matchSpecificTitles($mission, $candidates);
        if ($specific->isNotEmpty()) {
            return $this->mapResources($specific);
        }

        // Games: never show the whole day catalog — only named matches.
        if ($kind === 'game') {
            return [];
        }

        // Lectures: generic duty with no named topic → all lectures for this day.
        if ($this->isGenericLectureMission($mission)) {
            return $this->mapResources(
                $candidates->filter(fn ($r) => $r->event_program_day_id === null
                    || (int) $r->event_program_day_id === (int) $day->id)
            );
        }

        return [];
    }

    /**
     * @param  Collection<int, object>  $candidates
     * @return Collection<int, object>
     */
    private function matchSpecificTitles(string $mission, Collection $candidates): Collection
    {
        $haystacks = $this->missionSearchTokens($mission);
        $matched = collect();

        foreach ($candidates as $resource) {
            $title = trim((string) $resource->title);
            if ($title === '') {
                continue;
            }
            $normTitle = $this->normalize($title);
            if (mb_strlen($normTitle) < 2) {
                continue;
            }

            foreach ($haystacks as $token) {
                if ($token === '' || mb_strlen($token) < 2) {
                    continue;
                }
                if (str_contains($token, $normTitle)
                    || str_contains($normTitle, $token)
                    || $this->fuzzyContains($token, $normTitle)) {
                    $matched->push($resource);
                    break;
                }
            }
        }

        return $matched->unique('id')->values();
    }

    /** @return list<string> */
    private function missionSearchTokens(string $mission): array
    {
        $tokens = [$this->normalize($mission)];

        if (preg_match_all('/\(([^)]+)\)/u', $mission, $m)) {
            foreach ($m[1] as $inner) {
                $tokens[] = $this->normalize($inner);
            }
        }
        if (preg_match('/:\s*(.+)$/u', $mission, $m)) {
            $tokens[] = $this->normalize($m[1]);
        }

        $stripped = preg_replace(
            '/(كشفي|العاب|الألعاب|ألعاب|تحضير|محاضرة|lecture|game|games|توصيل\s*ال?هدف|دوري)/ui',
            ' ',
            $mission
        ) ?? $mission;
        $tokens[] = $this->normalize($stripped);

        return array_values(array_unique(array_filter($tokens)));
    }

    private function inferResourceKindFromMission(string $mission, string $slotKind): ?string
    {
        $lower = mb_strtolower($mission);

        $looksGame = $slotKind === 'games'
            || str_contains($mission, 'العاب')
            || str_contains($mission, 'ألعاب')
            || str_contains($lower, 'game')
            || str_contains($mission, 'تحضير العاب');

        $looksLecture = $slotKind === 'lecture'
            || str_contains($mission, 'كشفي')
            || str_contains($mission, 'محاضر')
            || str_contains($mission, 'توصيل هدف')
            || str_contains($lower, 'lecture')
            || str_contains($mission, 'نيران')
            || str_contains($mission, 'اسعاف')
            || str_contains($mission, 'صفافير')
            || str_contains($mission, 'مقطفي')
            || str_contains($mission, 'وجل');

        // Prefer lecture when mission is clearly a scout topic, even inside a games-ish slot.
        if ($looksLecture && ! $looksGame) {
            return 'lecture';
        }
        if ($looksGame && ! $looksLecture) {
            return 'game';
        }
        if ($looksLecture && $looksGame) {
            return str_contains($mission, 'كشفي') || str_contains($mission, 'محاضر') || str_contains($mission, 'توصيل')
                ? 'lecture'
                : 'game';
        }
        if ($slotKind === 'games') {
            return 'game';
        }
        if ($slotKind === 'lecture') {
            return 'lecture';
        }

        return null;
    }

    private function isGenericLectureMission(string $mission): bool
    {
        $trimmed = trim($mission);
        $n = $this->normalize($trimmed);

        foreach (['توصيل هدف', 'توصيل الهدف', 'محاضرة', 'lecture', 'كشفي', 'كشفي 1', 'كشفي 2', 'مساعده في الكشفي', 'مساعدة في الكشفي'] as $g) {
            if ($n === $this->normalize($g)) {
                return true;
            }
        }

        if (preg_match('/^كشفي\s*\d*$/u', $trimmed) === 1) {
            return true;
        }

        return str_contains($trimmed, 'توصيل هدف') && ! preg_match('/\([^)]+\)/u', $trimmed);
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = str_replace(['ـ'], '', $s);
        $s = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $s) ?? $s;
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return trim($s);
    }

    private function fuzzyContains(string $haystack, string $needle): bool
    {
        if ($needle === '' || $haystack === '') {
            return false;
        }
        if (mb_strlen($needle) >= 4 && str_contains($haystack, $needle)) {
            return true;
        }
        if (mb_strlen($haystack) >= 4 && str_contains($needle, $haystack)) {
            return true;
        }

        similar_text($haystack, $needle, $pct);

        return $pct >= 85.0 && min(mb_strlen($haystack), mb_strlen($needle)) >= 4;
    }

    /**
     * @param  Collection<int, object>  $resources
     * @return list<array{kind: string, title: string, url: ?string}>
     */
    private function mapResources(Collection $resources): array
    {
        return $resources->values()->map(fn ($r) => [
            'kind' => $r->kind,
            'title' => $r->title,
            'url' => $r->url,
        ])->all();
    }
}
