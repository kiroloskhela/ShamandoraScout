<?php

namespace Tests\Unit;

use App\Domain\WhatsApp\CampaignCsvImporter;
use RuntimeException;
use Tests\TestCase;

class CampaignCsvImporterTest extends TestCase
{
    private function parse(string $content): array
    {
        $path = sys_get_temp_dir().'/wa-csv-unit-'.uniqid('', true).'.csv';
        file_put_contents($path, $content);

        try {
            return app(CampaignCsvImporter::class)->parseUploadedFile($path);
        } finally {
            @unlink($path);
        }
    }

    public function test_parses_comma_separated_template(): void
    {
        $rows = $this->parse("\xEF\xBB\xBFPhone Number,Message\n1000485402,Hello one\n");

        $this->assertCount(1, $rows);
        $this->assertSame('+201000485402', $rows[0]['phone']);
        $this->assertSame('Hello one', $rows[0]['message']);
    }

    public function test_parses_excel_semicolon_locale_csv(): void
    {
        $rows = $this->parse("Phone Number;Message\n1000485402;رسالة تجريبية\n01012345678;Second\n");

        $this->assertCount(2, $rows);
        $this->assertSame('+201000485402', $rows[0]['phone']);
        $this->assertSame('رسالة تجريبية', $rows[0]['message']);
        $this->assertSame('+201012345678', $rows[1]['phone']);
    }

    public function test_parses_tab_separated_csv(): void
    {
        $rows = $this->parse("Phone Number\tMessage\n1000485402\tHi\n");

        $this->assertCount(1, $rows);
        $this->assertSame('+201000485402', $rows[0]['phone']);
    }

    public function test_rejects_single_column_csv(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Phone Number و Message');

        $this->parse("Phone Number\n1000485402\n");
    }
}
