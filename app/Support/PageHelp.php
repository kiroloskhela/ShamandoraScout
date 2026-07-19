<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class PageHelp
{
    /**
     * Resolve help content for the current (or given) route name.
     *
     * @return array{key: string, title: string, intro: string, steps: list<string>}
     */
    public static function content(?string $routeName = null): array
    {
        $routeName = $routeName ?: Route::currentRouteName();
        $key = self::resolveKey($routeName);

        if ($key === 'default') {
            return [
                'key' => 'default',
                'title' => (string) __('help.default.title'),
                'intro' => (string) __('help.default.intro'),
                'steps' => self::steps(__('help.default.steps')),
            ];
        }

        $entry = self::entries()[$key] ?? null;
        if (! is_array($entry)) {
            return self::content('__missing__');
        }

        return [
            'key' => $key,
            'title' => (string) ($entry['title'] ?? ''),
            'intro' => (string) ($entry['intro'] ?? ''),
            'steps' => self::steps($entry['steps'] ?? []),
        ];
    }

    public static function resolveKey(?string $routeName): string
    {
        if (! $routeName || $routeName === '__missing__') {
            return 'default';
        }

        $entries = self::entries();

        if (isset($entries[$routeName])) {
            return $routeName;
        }

        if (str_starts_with($routeName, 'person.new-enrolments-') && isset($entries['person.new-enrolments-index'])) {
            return 'person.new-enrolments-index';
        }

        if (str_starts_with($routeName, 'person.waiting-list') && isset($entries['person.waiting-list-index'])) {
            return 'person.waiting-list-index';
        }

        $parts = explode('.', $routeName);
        if (count($parts) >= 2) {
            foreach ([
                $parts[0].'.index',
                $parts[0].'.my',
                $parts[0].'.show',
            ] as $candidate) {
                if (isset($entries[$candidate])) {
                    return $candidate;
                }
            }
        }

        return 'default';
    }

    /**
     * @return array<string, array{title?: string, intro?: string, steps?: mixed}>
     */
    private static function entries(): array
    {
        $entries = __('help.entries');

        return is_array($entries) ? $entries : [];
    }

    /**
     * @param  mixed  $steps
     * @return list<string>
     */
    private static function steps(mixed $steps): array
    {
        if (! is_array($steps)) {
            return [];
        }

        return array_values(array_filter(
            $steps,
            static fn ($step) => is_string($step) && $step !== ''
        ));
    }
}
