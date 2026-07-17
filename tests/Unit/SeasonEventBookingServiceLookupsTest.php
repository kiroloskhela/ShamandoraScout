<?php

namespace Tests\Unit;

use App\Domain\EventFinance\SeasonEventBookingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeasonEventBookingServiceLookupsTest extends TestCase
{
    private SeasonEventBookingService $bookings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookings = new SeasonEventBookingService;

        Schema::dropIfExists('SeasonEventParticipantFinancePayment');
        Schema::dropIfExists('SeasonEventParticipantFinance');

        Schema::create('SeasonEventParticipantFinance', function (Blueprint $table) {
            $table->increments('SeasonEventParticipantFinanceID');
            $table->unsignedInteger('SeasonEventID')->default(1);
            $table->unsignedInteger('PersonID')->nullable();
            $table->unsignedInteger('GuestID')->nullable();
            $table->unsignedInteger('FamilyID')->nullable();
            $table->decimal('FinalRequiredAmount', 10, 2)->default(100);
            $table->unsignedInteger('InstallmentsNumber')->default(1);
            $table->decimal('AmountPaid', 10, 2)->default(0);
            $table->unsignedTinyInteger('IsRefunded')->default(0);
        });

        Schema::create('SeasonEventParticipantFinancePayment', function (Blueprint $table) {
            $table->increments('PaymentID');
            $table->unsignedInteger('SeasonEventParticipantFinanceID');
            $table->string('PaymentType')->default('PAYMENT');
            $table->dateTime('PaymentDate')->nullable();
        });
    }

    public function test_count_payments_only_counts_payment_type(): void
    {
        $bookingId = DB::table('SeasonEventParticipantFinance')->insertGetId([
            'SeasonEventID' => 1,
        ]);

        DB::table('SeasonEventParticipantFinancePayment')->insert([
            ['SeasonEventParticipantFinanceID' => $bookingId, 'PaymentType' => 'PAYMENT', 'PaymentDate' => '2026-01-01 10:00:00'],
            ['SeasonEventParticipantFinanceID' => $bookingId, 'PaymentType' => 'REFUND', 'PaymentDate' => '2026-01-02 10:00:00'],
            ['SeasonEventParticipantFinanceID' => $bookingId, 'PaymentType' => 'PAYMENT', 'PaymentDate' => '2026-01-03 10:00:00'],
        ]);

        $this->assertSame(2, $this->bookings->countPayments($bookingId));
    }

    public function test_is_last_payment(): void
    {
        $bookingId = DB::table('SeasonEventParticipantFinance')->insertGetId([
            'SeasonEventID' => 1,
        ]);

        $first = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
            'SeasonEventParticipantFinanceID' => $bookingId,
            'PaymentType' => 'PAYMENT',
            'PaymentDate' => '2026-01-01 10:00:00',
        ]);
        $second = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
            'SeasonEventParticipantFinanceID' => $bookingId,
            'PaymentType' => 'PAYMENT',
            'PaymentDate' => '2026-01-02 10:00:00',
        ]);

        $this->assertFalse($this->bookings->isLastPayment($bookingId, $first));
        $this->assertTrue($this->bookings->isLastPayment($bookingId, $second));
    }

    public function test_get_payment_with_booking(): void
    {
        $bookingId = DB::table('SeasonEventParticipantFinance')->insertGetId([
            'SeasonEventID' => 9,
            'PersonID' => 42,
            'FinalRequiredAmount' => 250,
            'InstallmentsNumber' => 3,
        ]);

        $paymentId = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
            'SeasonEventParticipantFinanceID' => $bookingId,
            'PaymentType' => 'PAYMENT',
            'PaymentDate' => '2026-01-01 10:00:00',
        ]);

        $row = $this->bookings->getPaymentWithBooking($paymentId);

        $this->assertNotNull($row);
        $this->assertSame(9, (int) $row->SeasonEventID);
        $this->assertSame(42, (int) $row->PersonID);
        $this->assertSame(3, (int) $row->InstallmentsNumber);
    }
}
