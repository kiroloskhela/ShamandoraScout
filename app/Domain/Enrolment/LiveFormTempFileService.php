<?php

namespace App\Domain\Enrolment;

use Illuminate\Support\Facades\Storage;

class LiveFormTempFileService
{
    public function finalizeTempFile(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            if (str_starts_with($path, 'person_images/')) {
                return null;
            }

            $permanent = 'person_images/'.basename($path);

            return $disk->exists($permanent) ? $permanent : null;
        }

        if (str_starts_with($path, 'person_images/')) {
            return $path;
        }

        $basename = basename($path);
        $target = 'person_images/'.$basename;

        if ($disk->exists($target)) {
            $target = 'person_images/'.uniqid().'_'.$basename;
        }

        $disk->move($path, $target);

        return $target;
    }

    /**
     * Drop abandoned enrolment uploads. Only files directly inside temp/liveform.
     */
    public function deleteExpiredTempFiles(int $hours = 24): int
    {
        $disk = Storage::disk('public');
        $directory = 'temp/liveform';

        if (! $disk->exists($directory)) {
            return 0;
        }

        $cutoff = now()->subHours($hours)->getTimestamp();
        $deleted = 0;

        foreach ($disk->files($directory) as $file) {
            if ($disk->lastModified($file) >= $cutoff) {
                continue;
            }

            $disk->delete($file);
            $deleted++;
        }

        return $deleted;
    }
}
