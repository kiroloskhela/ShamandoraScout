<?php

namespace Tests\Unit;

use App\Domain\Enrolment\LiveFormFieldNormalizer;
use PHPUnit\Framework\TestCase;

class LiveFormFieldNormalizerTest extends TestCase
{
    private LiveFormFieldNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new LiveFormFieldNormalizer;
    }

    public function test_normalize_arabic_name_collapses_variants(): void
    {
        $this->assertSame('احمد', $this->normalizer->normalizeArabicName('أحمد'));
        $this->assertSame('علي', $this->normalizer->normalizeArabicName('على'));
        $this->assertSame('فاطمه', $this->normalizer->normalizeArabicName('فاطمة'));
    }

    public function test_normalize_arabic_fields_only_touches_listed_keys(): void
    {
        $data = [
            'first_name' => 'أحمد',
            'keep' => 'أ',
        ];

        $out = $this->normalizer->normalizeArabicFields($data, ['first_name']);

        $this->assertSame('احمد', $out['first_name']);
        $this->assertSame('أ', $out['keep']);
    }

    public function test_clean_list_splits_and_dedupes(): void
    {
        $this->assertNull($this->normalizer->cleanList(null));
        $this->assertNull($this->normalizer->cleanList('  '));
        $this->assertSame('a, b', $this->normalizer->cleanList("a، b; a\nb"));
    }
}
