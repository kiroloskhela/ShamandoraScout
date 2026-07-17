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

        if (! Storage::disk('public')->exists($path)) {
            return $path;
        }

        if (str_starts_with($path, 'person_images/')) {
            return $path;
        }

        $basename = basename($path);
        $target = 'person_images/'.$basename;

        if (Storage::disk('public')->exists($target)) {
            $target = 'person_images/'.uniqid().'_'.$basename;
        }

        Storage::disk('public')->move($path, $target);

        return $target;
    }
}
