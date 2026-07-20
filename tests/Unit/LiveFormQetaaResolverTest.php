<?php

namespace Tests\Unit;

use App\Domain\Enrolment\LiveFormQetaaResolver;
use PHPUnit\Framework\TestCase;

class LiveFormQetaaResolverTest extends TestCase
{
    private LiveFormQetaaResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new LiveFormQetaaResolver;
    }

    public function test_bra3em_for_sana_3_to_4(): void
    {
        $result = $this->resolver->resolve(3, 'Male', false);

        $this->assertSame([[1, 'براعم', 'Male']], $result);
        $this->assertSame(1, $this->resolver->resolveYouthSectorId(3, 'Male'));
    }

    public function test_ashbal_vs_zahrat_by_gender(): void
    {
        $this->assertSame([[2, 'أشبال', 'Male']], $this->resolver->resolve(5, 'Male', false));
        $this->assertSame([[9, 'زهرات', 'Female']], $this->resolver->resolve(5, 'Female', false));
    }

    public function test_leaders_school_overrides_to_idad_qada(): void
    {
        $result = $this->resolver->resolve(15, 'Male', true);

        $this->assertSame([[10, 'اعداد قادة', 'Male']], $result);
    }

    public function test_jawwala_and_qada_for_higher_sana(): void
    {
        $result = $this->resolver->resolve(16, 'Female', false);

        $this->assertSame([
            [5, 'جوالة', 'Female'],
            [7, 'قادة', 'Female'],
        ], $result);
    }

    public function test_default_qada_fallback(): void
    {
        $this->assertSame([[7, 'قادة', 'Male']], $this->resolver->resolve(1, 'Male', false));
    }
}
