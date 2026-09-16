<?php

namespace App\Console\Commands;

use App\Support\PersonAvatar;
use App\Support\PersonPhotoThumbnail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class GeneratePhotoThumbnails extends Command
{
    protected $signature = 'photos:generate-thumbnails {--force : Regenerate even when a working thumbnail already exists}';

    protected $description = 'Compress stored personal photos into PersonSystemImageThumbnailPath without overwriting originals';

    public function handle(): int
    {
        if (! Schema::hasTable('PersonImages')) {
            $this->warn('PersonImages table missing — skipping.');

            return self::SUCCESS;
        }

        $force = (bool) $this->option('force');
        $processed = 0;
        $skipped = 0;
        $failed = 0;

        DB::table('PersonImages')
            ->orderBy('PersonID')
            ->chunk(100, function ($rows) use ($force, &$processed, &$skipped, &$failed) {
                foreach ($rows as $row) {
                    $original = trim((string) ($row->PersonSystemImagePath ?? ''));
                    $thumb = trim((string) ($row->PersonSystemImageThumbnailPath ?? ''));

                    if ($original === '') {
                        $skipped++;

                        continue;
                    }

                    if (! $force && PersonPhotoThumbnail::isWorking($thumb !== '' ? $thumb : null)) {
                        $skipped++;

                        continue;
                    }

                    if (PersonAvatar::localFile($original) === null) {
                        $skipped++;

                        continue;
                    }

                    $new = PersonPhotoThumbnail::storeFromOriginal($original, (int) $row->PersonID);
                    if ($new === null) {
                        $failed++;

                        continue;
                    }

                    if ($thumb !== '' && $thumb !== $new) {
                        PersonPhotoThumbnail::deleteStored($thumb);
                    }

                    DB::table('PersonImages')
                        ->where('PersonID', $row->PersonID)
                        ->update(['PersonSystemImageThumbnailPath' => $new]);

                    $processed++;
                }
            });

        $this->info("processed={$processed} skipped={$skipped} failed={$failed}");
        Log::info('photos.generate_thumbnails', [
            'processed' => $processed,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);

        return self::SUCCESS;
    }
}
