<?php

namespace App\Support;

class SafeHttpUrl
{
    public static function isSafe(?string $url): bool
    {
        $url = trim((string) $url);
        if ($url === '' || strlen($url) > 2000) {
            return false;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '';
    }

    public static function sanitize(?string $url): ?string
    {
        $url = trim((string) $url);

        return $url !== '' && self::isSafe($url) ? $url : null;
    }

    /**
     * @return list<mixed>
     */
    public static function rules(int $max = 2000): array
    {
        return [
            'nullable',
            'string',
            'max:'.$max,
            function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || $value === '') {
                    return;
                }
                if (! self::isSafe((string) $value)) {
                    $fail(__('The URL must be a valid http:// or https:// address.'));
                }
            },
        ];
    }
}
