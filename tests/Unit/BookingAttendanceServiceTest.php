<?php

namespace Tests\Unit;

use App\Services\BookingAttendanceService;
use Tests\TestCase;

class BookingAttendanceServiceTest extends TestCase
{
    public function test_allowed_statuses(): void
    {
        $this->assertSame(['present', 'absent', 'outside'], BookingAttendanceService::STATUSES);
    }
}
