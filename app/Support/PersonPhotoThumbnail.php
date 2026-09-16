<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PersonPhotoThumbnail
{
    public const DIRECTORY = 'persons/personal/thumbs';

    public const MAX_SIDE = 320;

    public const JPEG_QUALITY = 70;

    public static function isWorking(?string $thumbStoredPath): bool
    {
        $file = PersonAvatar::localFile($thumbStoredPath);

        return $file !== null && is_file($file) && filesize($file) > 0;
    }

    /**
     * Compress a stored original into a JPEG on the public disk. Does not overwrite the original.
     */
    public static function storeFromOriginal(?string $originalStoredPath, ?int $personId = null): ?string
    {
        $relative = self::diskRelative($originalStoredPath);
        if ($relative === null) {
            return null;
        }

        $source = PersonAvatar::localFile($originalStoredPath);
        if ($source === null) {
            return null;
        }

        $jpeg = self::compressToJpeg($source);
        if ($jpeg === null) {
            return null;
        }

        $dest = self::destinationPath($relative, $personId);
        if (! Storage::disk('public')->put($dest, $jpeg)) {
            return null;
        }

        return $dest;
    }

    public static function deleteStored(?string $storedPath): void
    {
        $relative = self::diskRelative($storedPath);
        if ($relative === null) {
            return;
        }

        Storage::disk('public')->delete($relative);
    }

    public static function diskRelative(?string $storedPath): ?string
    {
        if ($storedPath === null) {
            return null;
        }

        $path = trim($storedPath);
        if ($path === '' || preg_match('/^https?:\/\//i', $path)) {
            return null;
        }

        return ltrim((string) preg_replace('#^storage/#', '', ltrim($path, '/')), '/');
    }

    private static function destinationPath(string $originalRelative, ?int $personId): string
    {
        $stem = pathinfo(basename($originalRelative), PATHINFO_FILENAME);
        $stem = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $stem) ?: 'photo';
        $prefix = ($personId !== null && $personId > 0) ? $personId.'_' : '';

        return self::DIRECTORY.'/'.$prefix.$stem.'.jpg';
    }

    private static function compressToJpeg(string $absolutePath): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return null;
        }

        $bytes = @file_get_contents($absolutePath);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        $src = @imagecreatefromstring($bytes);
        if ($src === false) {
            return null;
        }

        $width = imagesx($src);
        $height = imagesy($src);
        if ($width < 1 || $height < 1) {
            imagedestroy($src);

            return null;
        }

        $scale = min(1.0, self::MAX_SIDE / max($width, $height));
        $newW = max(1, (int) round($width * $scale));
        $newH = max(1, (int) round($height * $scale));

        $dst = imagecreatetruecolor($newW, $newH);
        if ($dst === false) {
            imagedestroy($src);

            return null;
        }

        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($src);

        ob_start();
        $ok = imagejpeg($dst, null, self::JPEG_QUALITY);
        $jpeg = ob_get_clean();
        imagedestroy($dst);

        if ($ok === false || ! is_string($jpeg) || $jpeg === '') {
            return null;
        }

        return $jpeg;
    }
}
