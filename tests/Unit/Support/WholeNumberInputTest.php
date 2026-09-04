<?php

namespace Tests\Unit\Support;

use App\Support\WholeNumberInput;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class WholeNumberInputTest extends TestCase
{
    public function test_coerces_whole_decimals_to_int(): void
    {
        $this->assertSame(200, WholeNumberInput::coerce('200.00'));
        $this->assertSame(150, WholeNumberInput::coerce(' 150.00 '));
        $this->assertSame(200, WholeNumberInput::coerce(200.0));
        $this->assertSame(1, WholeNumberInput::coerce('1'));
        $this->assertSame(200, WholeNumberInput::coerce(200));
        $this->assertSame(0, WholeNumberInput::coerce('0.00'));
    }

    public function test_leaves_fractional_and_non_numeric_values_unchanged(): void
    {
        $this->assertSame('200.50', WholeNumberInput::coerce('200.50'));
        $this->assertSame('200.99', WholeNumberInput::coerce('200.99'));
        $this->assertSame('2e2', WholeNumberInput::coerce('2e2'));
        $this->assertSame('abc', WholeNumberInput::coerce('abc'));
        $this->assertSame('', WholeNumberInput::coerce(''));
        $this->assertNull(WholeNumberInput::coerce(null));
        $this->assertSame(-5, WholeNumberInput::coerce('-5.00'));
    }

    public function test_coerced_whole_decimals_pass_integer_rule_without_truncating_cents(): void
    {
        $this->assertTrue(
            Validator::make(['price' => WholeNumberInput::coerce('200.00')], ['price' => 'integer'])->passes()
        );
        $this->assertTrue(
            Validator::make(['price' => WholeNumberInput::coerce('200.50')], ['price' => 'integer'])->fails()
        );
    }
}
