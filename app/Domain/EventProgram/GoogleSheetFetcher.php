<?php

namespace App\Domain\EventProgram;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class GoogleSheetFetcher
{
    /**
     * Download a Google Sheet as xlsx into $targetPath.
     * Sheet must be shared as "Anyone with the link can view".
     */
    public function downloadXlsx(string $shareUrl, string $targetPath): string
    {
        $id = $this->extractSpreadsheetId($shareUrl);
        if (! $id) {
            throw new RuntimeException('رابط Google Sheets غير صالح.');
        }

        $export = "https://docs.google.com/spreadsheets/d/{$id}/export?format=xlsx";
        $response = Http::timeout(120)
            ->withOptions(['allow_redirects' => true])
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ShamandoraScout/EventProgram)'])
            ->get($export);

        if (! $response->successful()) {
            throw new RuntimeException('تعذر تنزيل الشيت. تأكد أنه متاح للعرض عبر الرابط.');
        }

        $body = $response->body();
        if (strlen($body) < 100 || substr($body, 0, 2) !== 'PK') {
            throw new RuntimeException('الاستجابة ليست ملف Excel. ربما الشيت غير عام.');
        }

        if (! is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0775, true);
        }
        file_put_contents($targetPath, $body);

        return $targetPath;
    }

    public function extractSpreadsheetId(string $url): ?string
    {
        if (preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#^[a-zA-Z0-9-_]{20,}$#', trim($url))) {
            return trim($url);
        }

        return null;
    }
}
