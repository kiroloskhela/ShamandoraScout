<?php

namespace App\Domain\Enrolment;

class LiveFormFieldNormalizer
{
    public function normalizeArabicName(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        $search = [
            'أ', 'إ', 'آ', 'ٱ',
            'ى', 'ئ', 'ي',
            'ؤ',
            'ة',
            'چ',
        ];

        $replace = [
            'ا', 'ا', 'ا', 'ا',
            'ي', 'ي', 'ي',
            'و',
            'ه',
            'ج',
        ];

        $value = str_replace($search, $replace, $value);

        $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $value);
        $value = preg_replace('/[\x{200E}\x{200F}\x{061C}\x{202A}-\x{202E}]/u', '', $value);
        $value = preg_replace('/[^\p{Arabic}\s]/u', '', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    public function normalizeArabicFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->normalizeArabicName($data[$field]);
            }
        }

        return $data;
    }

    public function cleanList(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(["\r\n", "\n", '،', ';'], ',', $value);

        $parts = array_filter(array_map('trim', explode(',', $value)), function ($x) {
            return $x !== '';
        });

        $parts = array_values(array_unique($parts));

        return count($parts) ? implode(', ', $parts) : null;
    }
}
