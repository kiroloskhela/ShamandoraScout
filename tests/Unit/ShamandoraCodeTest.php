<?php

namespace Tests\Unit;

use App\Support\ShamandoraCode;
use PHPUnit\Framework\TestCase;

class ShamandoraCodeTest extends TestCase
{
    public function test_formats_person_id_with_zero_padding(): void
    {
        $this->assertSame('SH-00001', ShamandoraCode::fromPersonId(1));
        $this->assertSame('SH-01688', ShamandoraCode::fromPersonId(1688));
        $this->assertSame('SH-123456', ShamandoraCode::fromPersonId(123456));
        $this->assertSame('SH-00042', ShamandoraCode::forPersonId(42));
    }
}
