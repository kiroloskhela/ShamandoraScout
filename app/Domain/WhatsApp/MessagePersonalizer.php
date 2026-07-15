<?php

namespace App\Domain\WhatsApp;

/**
 * Replace {name} (and future tokens) in campaign templates.
 */
class MessagePersonalizer
{
    public const VAR_NAME = 'name';

    /**
     * @return list<string>
     */
    public static function availableVariables(): array
    {
        return [self::VAR_NAME];
    }

    /**
     * @param  array{FirstName?:?string,SecondName?:?string,ThirdName?:?string,name?:?string}  $person
     * @return array{message: string, missing: list<string>, skipped: bool}
     */
    public function personalize(
        string $template,
        array $person,
        string $missingBehavior = 'fallback',
        ?string $fallbackName = null
    ): array {
        $missing = [];
        $skipped = false;

        $name = $this->resolveName($person);
        if ($name === '') {
            $missing[] = self::VAR_NAME;
            if ($missingBehavior === 'skip') {
                return ['message' => '', 'missing' => $missing, 'skipped' => true];
            }
            if ($missingBehavior === 'fallback') {
                $name = trim((string) ($fallbackName ?? 'صديقنا'));
            }
            // empty / warn: leave blank replacement
        }

        $replacements = [
            '{name}' => $name,
            '{NAME}' => $name,
        ];

        $message = strtr($template, $replacements);

        // Any leftover {token} counts as unresolved
        if (preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $message, $m)) {
            foreach ($m[1] as $token) {
                if (!in_array($token, self::availableVariables(), true) && !in_array($token, $missing, true)) {
                    $missing[] = $token;
                }
            }
        }

        if ($missing !== [] && $missingBehavior === 'skip' && $skipped === false && $name === '') {
            $skipped = true;
        }

        return [
            'message' => $message,
            'missing' => array_values(array_unique($missing)),
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  array{FirstName?:?string,SecondName?:?string,ThirdName?:?string,name?:?string}  $person
     */
    public function resolveName(array $person): string
    {
        if (!empty($person['name'])) {
            return trim((string) $person['name']);
        }

        return trim(implode(' ', array_filter([
            $person['FirstName'] ?? null,
            $person['SecondName'] ?? null,
            $person['ThirdName'] ?? null,
        ], fn ($p) => $p !== null && trim((string) $p) !== '')));
    }
}
