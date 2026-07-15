<?php

namespace App\Domain\WhatsApp;

use App\Services\WhatsAppBridgeClient;
use RuntimeException;

/**
 * Parse CSV uploads for per-number custom WhatsApp campaign messages.
 *
 * Expected columns (header, case-insensitive):
 *   - Phone Number (aliases: phone, phone_number, number, mobile)
 *   - Message (aliases: message, msg, text, body)
 *
 * Delimiter is auto-detected (comma, semicolon, or tab) so Excel locale
 * exports that use ";" still work.
 */
class CampaignCsvImporter
{
    public const MAX_ROWS = 2000;

    public function __construct(
        private readonly WhatsAppBridgeClient $bridge,
    ) {
    }

    /**
     * @return list<array{phone: string, message: string, row: int}>
     */
    public function parseUploadedFile(string $absolutePath): array
    {
        if (!is_readable($absolutePath)) {
            throw new RuntimeException('تعذر قراءة ملف CSV.');
        }

        $raw = file_get_contents($absolutePath);
        if ($raw === false || $raw === '') {
            throw new RuntimeException('ملف CSV فارغ.');
        }

        // Normalize encodings Excel sometimes writes
        if (str_starts_with($raw, "\xFF\xFE") || str_starts_with($raw, "\xFE\xFF")) {
            $converted = @mb_convert_encoding($raw, 'UTF-8', 'UTF-16');
            if (is_string($converted) && $converted !== '') {
                $raw = $converted;
            }
        }

        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;

        $firstLine = strtok($raw, "\r\n") ?: '';
        $delimiter = $this->detectDelimiter($firstLine);

        $handle = fopen('php://memory', 'r+b');
        if ($handle === false) {
            throw new RuntimeException('تعذر فتح ملف CSV.');
        }

        fwrite($handle, $raw);
        rewind($handle);

        try {
            $header = fgetcsv($handle, 0, $delimiter);
            if ($header === false || $header === [null] || $header === []) {
                throw new RuntimeException('ملف CSV فارغ.');
            }

            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];
            }

            $map = $this->mapColumns($header);
            if ($map['phone'] === null || $map['message'] === null) {
                $found = implode(' | ', array_map(
                    static fn ($h) => trim((string) $h),
                    $header
                ));
                throw new RuntimeException(
                    'يجب أن يحتوي CSV على عمودين: Phone Number و Message. العناوين الموجودة: '.$found
                );
            }

            $rows = [];
            $seenPhones = [];
            $line = 1;

            while (($cols = fgetcsv($handle, 0, $delimiter)) !== false) {
                $line++;
                if ($cols === [null] || $this->rowEmpty($cols)) {
                    continue;
                }

                $phoneRaw = trim((string) ($cols[$map['phone']] ?? ''));
                $message = trim((string) ($cols[$map['message']] ?? ''));

                if ($phoneRaw === '' && $message === '') {
                    continue;
                }

                if ($phoneRaw === '' || $message === '') {
                    throw new RuntimeException("صف {$line}: رقم الهاتف والرسالة مطلوبان.");
                }

                if (mb_strlen($message) > 4000) {
                    throw new RuntimeException("صف {$line}: الرسالة أطول من 4000 حرف.");
                }

                $phone = $this->bridge->normalizeEgNumber($phoneRaw);
                $digitLen = strlen(preg_replace('/\D+/', '', $phone) ?? '');

                if ($phone === '+2' || $digitLen < 12) {
                    throw new RuntimeException(
                        "صف {$line}: رقم هاتف غير صالح ({$phoneRaw}). مثال صحيح: 1000485402 أو 01000485402"
                    );
                }

                if (isset($seenPhones[$phone])) {
                    throw new RuntimeException(
                        "صف {$line}: رقم مكرر ({$phone}) — ظهر أيضاً في صف {$seenPhones[$phone]}."
                    );
                }
                $seenPhones[$phone] = $line;

                $rows[] = [
                    'phone' => $phone,
                    'message' => $message,
                    'row' => $line,
                ];

                if (count($rows) > self::MAX_ROWS) {
                    throw new RuntimeException('الحد الأقصى ' . self::MAX_ROWS . ' رقم في الملف الواحد.');
                }
            }

            if ($rows === []) {
                throw new RuntimeException('لا توجد صفوف صالحة في ملف CSV.');
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /**
     * Prefer the delimiter that yields recognizable Phone/Message headers.
     */
    private function detectDelimiter(string $headerLine): string
    {
        $headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine) ?? $headerLine;
        $best = ',';
        $bestScore = -1;

        foreach ([',', ';', "\t"] as $delimiter) {
            $cols = str_getcsv($headerLine, $delimiter);
            if (count($cols) < 2) {
                continue;
            }

            $map = $this->mapColumns($cols);
            $score = count($cols);
            if ($map['phone'] !== null) {
                $score += 10;
            }
            if ($map['message'] !== null) {
                $score += 10;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $delimiter;
            }
        }

        return $best;
    }

    /**
     * @param  list<string|null>  $header
     * @return array{phone: ?int, message: ?int}
     */
    private function mapColumns(array $header): array
    {
        $phone = null;
        $message = null;

        foreach ($header as $i => $raw) {
            $key = $this->normalizeHeader((string) $raw);
            if (in_array($key, [
                'phone number', 'phone_number', 'phonenumber', 'phone', 'number', 'mobile',
                'رقم الهاتف', 'رقم', 'موبايل',
            ], true)) {
                $phone = (int) $i;
            }
            if (in_array($key, [
                'message', 'messages', 'msg', 'text', 'body',
                'الرسالة', 'رساله', 'رسالة',
            ], true)) {
                $message = (int) $i;
            }
        }

        // Fallback: first two columns when headers are unrecognized but present
        if ($phone === null && $message === null && count($header) >= 2) {
            $phone = 0;
            $message = 1;
        }

        return ['phone' => $phone, 'message' => $message];
    }

    private function normalizeHeader(string $value): string
    {
        $value = trim(mb_strtolower($value));
        // Non-breaking / exotic spaces from Excel
        $value = preg_replace('/[\x{00A0}\x{2007}\x{202F}]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }

    /**
     * @param  list<string|null>  $cols
     */
    private function rowEmpty(array $cols): bool
    {
        foreach ($cols as $c) {
            if (trim((string) $c) !== '') {
                return false;
            }
        }

        return true;
    }
}
