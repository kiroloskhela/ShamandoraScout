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

        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('تعذر فتح ملف CSV.');
        }

        try {
            $header = fgetcsv($handle);
            if ($header === false || $header === [null] || $header === []) {
                throw new RuntimeException('ملف CSV فارغ.');
            }

            // Strip UTF-8 BOM from first cell
            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];
            }

            $map = $this->mapColumns($header);
            if ($map['phone'] === null || $map['message'] === null) {
                throw new RuntimeException(
                    'يجب أن يحتوي CSV على عمودين: Phone Number و Message.'
                );
            }

            $rows = [];
            $seenPhones = [];
            $line = 1;

            while (($cols = fgetcsv($handle)) !== false) {
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
     * @param  list<string|null>  $header
     * @return array{phone: ?int, message: ?int}
     */
    private function mapColumns(array $header): array
    {
        $phone = null;
        $message = null;

        foreach ($header as $i => $raw) {
            $key = $this->normalizeHeader((string) $raw);
            if (in_array($key, ['phone number', 'phone_number', 'phonenumber', 'phone', 'number', 'mobile', 'رقم الهاتف', 'رقم'], true)) {
                $phone = (int) $i;
            }
            if (in_array($key, ['message', 'msg', 'text', 'body', 'الرسالة', 'رساله'], true)) {
                $message = (int) $i;
            }
        }

        // Fallback: first two columns if headers are exactly those English labels with spaces quirks
        if ($phone === null && $message === null && count($header) >= 2) {
            $phone = 0;
            $message = 1;
        }

        return ['phone' => $phone, 'message' => $message];
    }

    private function normalizeHeader(string $value): string
    {
        $value = trim(mb_strtolower($value));
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
