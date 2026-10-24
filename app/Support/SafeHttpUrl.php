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

        if (! self::passesUrlFilter($url)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '';
    }

    /**
     * FILTER_VALIDATE_URL rejects non-ASCII paths. Encode only those path
     * segments, then validate the ASCII form. The original URL is kept.
     */
    private static function passesUrlFilter(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return true;
        }

        $parts = parse_url($url);
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        $path = $parts['path'] ?? '';
        if ($path === '' || preg_match('/[^\x00-\x7F]/', $path) !== 1) {
            return false;
        }

        $encodedPath = implode('/', array_map(static function (string $segment): string {
            return preg_match('/[^\x00-\x7F]/', $segment) === 1
                ? rawurlencode($segment)
                : $segment;
        }, explode('/', $path)));

        $scheme = $parts['scheme'].'://';
        $user = $parts['user'] ?? '';
        $pass = isset($parts['pass']) ? ':'.$parts['pass'] : '';
        $auth = $user !== '' ? $user.$pass.'@' : '';
        $host = $parts['host'];
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return filter_var(
            $scheme.$auth.$host.$port.$encodedPath.$query.$fragment,
            FILTER_VALIDATE_URL
        ) !== false;
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
