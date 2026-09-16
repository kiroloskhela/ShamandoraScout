<?php

namespace Tests\Unit;

use App\Http\Controllers\ExportController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;
use ZipArchive;

class ServedPeopleExportXlsxTest extends TestCase
{
    private const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function test_xlsx_zip_is_valid_with_png_drawing_and_without_photos(): void
    {
        $png = $this->tempFile('png');
        file_put_contents($png, base64_decode(self::TINY_PNG));

        $row = [
            'PersonID' => 1,
            'ShamandoraCode' => 'S1',
            'FullName' => 'Test Scout',
            'ImageLink' => 'https://example.test/photo.jpg',
            'Photo' => '',
        ];

        $withPhotos = $this->writeWorkbook([[
            'title' => 'Personal photos',
            'rows' => [$row],
            'photos' => [0 => $png],
        ]]);
        $withoutPhotos = $this->writeWorkbook([[
            'title' => 'Personal photos',
            'rows' => [$row],
        ]]);

        try {
            $this->assertValidXlsx($withPhotos, expectedMedia: 1);
            $this->assertValidXlsx($withoutPhotos, expectedMedia: 0);
        } finally {
            @unlink($withPhotos);
            @unlink($withoutPhotos);
            @unlink($png);
        }
    }

    public function test_webp_and_truncated_images_are_skipped_and_xlsx_stays_valid(): void
    {
        $webp = $this->tempFile('jpg');
        $this->writeTinyWebp($webp);
        $empty = $this->tempFile('png');
        file_put_contents($empty, '');
        $truncated = $this->tempFile('jpg');
        file_put_contents($truncated, "\xFF\xD8\xFF\xE0".str_repeat('x', 32));

        $this->assertFalse(ExportController::isEmbeddableImage($webp));
        $this->assertFalse(ExportController::isEmbeddableImage($empty));
        $this->assertFalse(ExportController::isEmbeddableImage($truncated));
        $this->assertFalse(ExportController::isEmbeddableImage('/no/such/photo.jpg'));

        $path = $this->writeWorkbook([[
            'title' => 'Personal photos',
            'rows' => [[
                'PersonID' => 1,
                'ShamandoraCode' => 'S1',
                'FullName' => 'Test Scout',
                'ImageLink' => 'https://example.test/photo.jpg',
                'Photo' => '',
            ]],
            'photos' => [0 => $webp, 1 => $empty, 2 => $truncated],
        ]]);

        try {
            $this->assertValidXlsx($path, expectedMedia: 0);
        } finally {
            @unlink($path);
            @unlink($webp);
            @unlink($empty);
            @unlink($truncated);
        }
    }

    public function test_tiny_png_is_embeddable(): void
    {
        $png = $this->tempFile('png');
        file_put_contents($png, base64_decode(self::TINY_PNG));

        try {
            $this->assertTrue(ExportController::isEmbeddableImage($png));
        } finally {
            @unlink($png);
        }
    }

    /**
     * @param  list<array{title: string, rows: list<array<string, mixed>>, photos?: array<int, string>}>  $sheets
     */
    private function writeWorkbook(array $sheets): string
    {
        $controller = app(ExportController::class);
        $workbook = ['sheets' => $sheets];
        $make = new \ReflectionMethod($controller, 'makeSpreadsheet');
        $write = new \ReflectionMethod($controller, 'writeXlsx');

        return $write->invoke($controller, $make->invoke($controller, $workbook), $workbook);
    }

    private function assertValidXlsx(string $path, int $expectedMedia): void
    {
        $this->assertFileExists($path);
        $this->assertSame('PK', substr((string) file_get_contents($path, false, null, 0, 2), 0, 2));

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true, 'xlsx is not a valid zip');
        $media = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            $this->assertStringEndsNotWith('.webp', strtolower($name));
            if (str_starts_with($name, 'xl/media/')) {
                $media++;
            }
        }
        $zip->close();
        $this->assertSame($expectedMedia, $media);

        $loaded = IOFactory::load($path);
        $this->assertSame('Personal photos', $loaded->getSheet(0)->getTitle());
        $this->assertSame('Test Scout', (string) $loaded->getSheet(0)->getCell('C2')->getValue());
        $loaded->disconnectWorksheets();
    }

    private function tempFile(string $ext): string
    {
        $path = tempnam(sys_get_temp_dir(), 'served_img_');
        $named = $path.'.'.$ext;
        @unlink($path);

        return $named;
    }

    private function writeTinyWebp(string $path): void
    {
        if (function_exists('imagewebp')) {
            $im = imagecreatetruecolor(1, 1);
            $this->assertTrue(imagewebp($im, $path));
            imagedestroy($im);

            return;
        }

        file_put_contents($path, base64_decode('UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEADsD+JaQAA3AAAAAA'));
    }
}
