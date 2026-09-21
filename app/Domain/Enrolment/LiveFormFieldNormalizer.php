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

    /**
     * @return list<string>
     */
    public function listParts(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        $value = trim($value);

        if ($value === '') {
            return [];
        }

        $value = str_replace(["\r\n", "\n", '،', ';'], ',', $value);

        $parts = array_filter(array_map('trim', explode(',', $value)), function ($x) {
            return $x !== '' && ! $this->isNegativeAnswer($x);
        });

        return array_values(array_unique($parts));
    }

    public function cleanList(?string $value): ?string
    {
        $parts = $this->listParts($value);

        return $parts === [] ? null : implode(', ', $parts);
    }

    public function isNegativeAnswer(?string $value): bool
    {
        if ($value === null) {
            return true;
        }

        $compact = $this->compactAnswer($value);

        if ($compact === '') {
            return true;
        }

        return in_array($compact, [
            'no',
            'none',
            'nil',
            'na',
            'nada',
            'nothing',
            'لا',
            'لايوجد',
            'لاشئ',
            'لاشيء',
            'لاشي',
            'بدون',
            'مفيش',
            'مشموجود',
        ], true);
    }

    private function compactAnswer(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['أ', 'إ', 'آ', 'ٱ', 'ة', 'ى', 'ئ', 'ء'], ['ا', 'ا', 'ا', 'ا', 'ه', 'ي', 'ي', ''], $value);

        return preg_replace('/[\s.،,;:_\\-\/\\\\]+/u', '', $value) ?? '';
    }
}
