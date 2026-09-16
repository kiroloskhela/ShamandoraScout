<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GeneratePhotoThumbnailsCommandTest extends TestCase
{
    private const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            $this->markTestSkipped('GD jpeg support required');
        }
        $this->createSchema();
    }

    public function test_command_writes_thumbnail_and_is_idempotent(): void
    {
        $original = $this->writeOriginal('thumb-cmd.png');
        DB::table('PersonImages')->insert([
            'PersonID' => 1,
            'PersonSystemImagePath' => 'persons/personal/thumb-cmd.png',
        ]);

        try {
            $this->artisan('photos:generate-thumbnails')
                ->expectsOutput('processed=1 skipped=0 failed=0')
                ->assertSuccessful();

            $row = DB::table('PersonImages')->where('PersonID', 1)->first();
            $this->assertSame('persons/personal/thumbs/1_thumb-cmd.jpg', $row->PersonSystemImageThumbnailPath);
            $thumb = storage_path('app/public/'.$row->PersonSystemImageThumbnailPath);
            $this->assertFileExists($thumb);
            $this->assertFileExists($original);
            $this->assertSame("\xFF\xD8", substr((string) file_get_contents($thumb), 0, 2));

            $this->artisan('photos:generate-thumbnails')
                ->expectsOutput('processed=0 skipped=1 failed=0')
                ->assertSuccessful();
            $this->assertSame(
                'persons/personal/thumbs/1_thumb-cmd.jpg',
                DB::table('PersonImages')->where('PersonID', 1)->value('PersonSystemImageThumbnailPath')
            );
        } finally {
            $this->cleanupPhotos();
        }
    }

    public function test_command_skips_missing_originals(): void
    {
        DB::table('PersonImages')->insert([
            'PersonID' => 2,
            'PersonSystemImagePath' => 'persons/personal/does-not-exist.jpg',
        ]);

        $this->artisan('photos:generate-thumbnails')
            ->expectsOutput('processed=0 skipped=1 failed=0')
            ->assertSuccessful();

        $this->assertNull(DB::table('PersonImages')->where('PersonID', 2)->value('PersonSystemImageThumbnailPath'));
    }

    private function writeOriginal(string $name): string
    {
        $dir = storage_path('app/public/persons/personal');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = $dir.'/'.$name;
        file_put_contents($file, base64_decode(self::TINY_PNG));

        return $file;
    }

    private function cleanupPhotos(): void
    {
        @unlink(storage_path('app/public/persons/personal/thumb-cmd.png'));
        @unlink(storage_path('app/public/persons/personal/thumbs/1_thumb-cmd.jpg'));
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('PersonImages');
        Schema::create('PersonImages', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('PersonSystemImagePath')->nullable();
            $table->string('PersonSystemImageThumbnailPath')->nullable();
        });
    }
}
