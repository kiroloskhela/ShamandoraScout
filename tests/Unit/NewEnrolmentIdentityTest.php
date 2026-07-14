<?php

namespace Tests\Unit;

use App\Support\NewEnrolmentIdentity;
use PHPUnit\Framework\TestCase;

/**
 * Covers the pure legacy-fallback PersonID logic used by
 * PersonNewController::allocateNewEnrolmentRecord() when the Package A
 * surrogate `id` column isn't present yet (see NewEnrolmentIdentity).
 *
 * hasAutoIncrementSurrogateId() itself needs a real DB connection
 * (Schema/information_schema), so it's intentionally left to feature-level
 * coverage against a real schema rather than unit tests here.
 */
class NewEnrolmentIdentityTest extends TestCase
{
    public function test_starts_at_one_when_table_is_empty(): void
    {
        $this->assertSame(1, NewEnrolmentIdentity::nextLegacyPersonId(null));
    }

    public function test_increments_the_current_max(): void
    {
        $this->assertSame(2, NewEnrolmentIdentity::nextLegacyPersonId(1));
        $this->assertSame(1689, NewEnrolmentIdentity::nextLegacyPersonId(1688));
    }

    public function test_handles_a_max_of_zero(): void
    {
        // 0 is a legitimate (if unusual) "current max" and must not be
        // confused with the "table is empty" (null) case.
        $this->assertSame(1, NewEnrolmentIdentity::nextLegacyPersonId(0));
    }
}
