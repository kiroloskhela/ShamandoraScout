<?php

namespace App\Support;

class PersonAvatar
{
    public const MALE_ASSET = 'img/avatars/default-male.png';

    public const FEMALE_ASSET = 'img/avatars/default-female.png';

    /**
     * Resolve a public avatar URL for a person photo path + gender.
     */
    public static function url(?string $storedPath, mixed $gender = null): string
    {
        $path = self::normalizeStoredPath($storedPath);
        if ($path !== null) {
            if (preg_match('/^https?:\/\//i', $path)) {
                return $path;
            }

            return asset($path);
        }

        return self::defaultUrl($gender);
    }

    public static function defaultUrl(mixed $gender = null): string
    {
        return asset(self::isFemale($gender) ? self::FEMALE_ASSET : self::MALE_ASSET);
    }

    public static function isFemale(mixed $gender): bool
    {
        $g = mb_strtolower(trim((string) $gender));

        return in_array($g, [
            'f',
            'female',
            'انثى',
            'أنثى',
            'اثنى',
            'أنثي',
            'انثي',
            'fem',
            'بنات',
            'فتاة',
            'بنت',
        ], true);
    }

    private static function normalizeStoredPath(?string $storedPath): ?string
    {
        if ($storedPath === null) {
            return null;
        }

        $path = trim($storedPath);
        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        $path = preg_replace('#^storage/#', '', ltrim($path, '/'));

        if (
            str_starts_with($path, 'uploads/') ||
            str_starts_with($path, 'img/') ||
            str_starts_with($path, 'storage/')
        ) {
            return $path;
        }

        return 'storage/'.ltrim($path, '/');
    }
}
