<?php

namespace Tests\Unit;

use App\Domain\PlaceBooking\PlaceBookingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlaceBookingServiceTest extends TestCase
{
    private PlaceBookingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PlaceBookings');

        Schema::create('PlaceBookings', function (Blueprint $table) {
            $table->increments('BookingID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('PlaceID');
            $table->unsignedInteger('QetaaID')->nullable();
            $table->date('BookingDate');
            $table->string('TimeFrom');
            $table->string('TimeTo');
            $table->text('UserNote')->nullable();
            $table->string('Status')->default('pending');
            $table->text('AdminNote')->nullable();
            $table->unsignedInteger('ReviewedBy')->nullable();
            $table->timestamp('ReviewedAt')->nullable();
            $table->unsignedInteger('ApprovedPlaceID')->nullable();
            $table->string('ApprovedTimeFrom')->nullable();
            $table->string('ApprovedTimeTo')->nullable();
            $table->timestamps();
        });

        $this->service = new PlaceBookingService();
    }

    public function test_create_inserts_pending_booking(): void
    {
        $bookingId = $this->service->create(
            42,
            10,
            3,
            '2026-01-28',
            '08:00',
            '09:00',
            'Team meeting'
        );

        $this->assertSame(1, $bookingId);

        $booking = DB::table('PlaceBookings')->where('BookingID', $bookingId)->first();
        $this->assertNotNull($booking);
        $this->assertSame(42, (int) $booking->PersonID);
        $this->assertSame(10, (int) $booking->PlaceID);
        $this->assertSame(3, (int) $booking->QetaaID);
        $this->assertSame('2026-01-28', $booking->BookingDate);
        $this->assertSame('08:00', $booking->TimeFrom);
        $this->assertSame('09:00', $booking->TimeTo);
        $this->assertSame('Team meeting', $booking->UserNote);
        $this->assertSame('pending', $booking->Status);
        $this->assertNull($booking->ApprovedPlaceID);
    }
}
