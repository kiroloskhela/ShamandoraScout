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

    public function test_clean_list_drops_negative_placeholder_answers(): void
    {
        $this->assertNull($this->normalizer->cleanList('لا'));
        $this->assertNull($this->normalizer->cleanList('لا يوجد'));
        $this->assertNull($this->normalizer->cleanList('لايوجد'));
        $this->assertNull($this->normalizer->cleanList('No'));
        $this->assertNull($this->normalizer->cleanList('none'));
        $this->assertNull($this->normalizer->cleanList('n/a'));
        $this->assertNull($this->normalizer->cleanList('N.A.'));
        $this->assertNull($this->normalizer->cleanList('nothing'));
        $this->assertNull($this->normalizer->cleanList('بدون'));
        $this->assertNull($this->normalizer->cleanList('مفيش'));
        $this->assertNull($this->normalizer->cleanList('لا شئ'));
        $this->assertNull($this->normalizer->cleanList('لا شيء'));
        $this->assertSame('لبن', $this->normalizer->cleanList('لبن, لا, لا يوجد'));
        $this->assertSame(['لبن'], $this->normalizer->listParts('لبن، لا'));
        $this->assertTrue($this->normalizer->isNegativeAnswer('لا يوجد.'));
        $this->assertFalse($this->normalizer->isNegativeAnswer('ربو'));
        $this->assertFalse($this->normalizer->isNegativeAnswer('لبن'));
    }
}
