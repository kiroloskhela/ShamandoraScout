<?php

namespace Tests\Unit;

use App\Domain\EventFinance\SeasonEventBookingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeasonEventBookingZeroPaymentTest extends TestCase
{
    private SeasonEventBookingService $bookings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookings = new SeasonEventBookingService;
        $this->createSchema();
        $this->seedEvent(price: 200, maxInstallments: 1, minimumDeposit: 50);
    }

    public function test_person_akhoh_rab_full_discount_allows_zero_first_payment(): void
    {
        $result = $this->bookings->createBooking(1, [
            'booking_type' => 'PERSON',
            'person_id' => 7,
            'first_payment_date' => '2026-08-20',
            'first_payment_amount' => 0,
            'is_not_able_to_pay_all' => 1,
            'special_case_type' => 'AKHOH_RAB',
            'discount_amount' => 200,
        ], 7);

        $this->assertTrue($result['ok']);
        $booking = DB::table('SeasonEventParticipantFinance')->first();
        $this->assertSame(0, (int) $booking->FinalRequiredAmount);
        $this->assertSame(0, (int) $booking->AmountPaid);
        $this->assertSame(0, (int) $booking->RemainingAmount);
        $this->assertSame('AKHOH_RAB', $booking->SpecialCaseType);
    }

    public function test_full_discount_allows_zero_first_payment(): void
    {
        $result = $this->bookings->createBooking(1, $this->guestPayload([
            'first_payment_amount' => 0,
            'discount_amount' => 200,
        ]), 7);

        $this->assertTrue($result['ok']);
        $this->assertArrayHasKey('payment_id', $result);

        $booking = DB::table('SeasonEventParticipantFinance')->first();
        $this->assertSame(200, (int) $booking->OriginalPrice);
        $this->assertSame(200, (int) $booking->DiscountAmount);
        $this->assertSame(0, (int) $booking->FinalRequiredAmount);
        $this->assertSame(0, (int) $booking->AmountPaid);
        $this->assertSame(0, (int) $booking->RemainingAmount);

        $payment = DB::table('SeasonEventParticipantFinancePayment')->first();
        $this->assertSame(0, (int) $payment->Amount);
        $this->assertSame(1, (int) $payment->InstallmentNumber);
    }

    public function test_zero_first_payment_is_rejected_when_amount_remains(): void
    {
        $result = $this->bookings->createBooking(1, $this->guestPayload([
            'first_payment_amount' => 0,
            'discount_amount' => 0,
        ]), 7);

        $this->assertFalse($result['ok']);
        $this->assertSame('first_payment_amount', $result['field']);
        $this->assertSame(0, DB::table('SeasonEventParticipantFinance')->count());
    }

    public function test_discount_greater_than_price_is_rejected(): void
    {
        $result = $this->bookings->createBooking(1, $this->guestPayload([
            'first_payment_amount' => 0,
            'discount_amount' => 250,
        ]), 7);

        $this->assertFalse($result['ok']);
        $this->assertSame('discount_amount', $result['field']);
        $this->assertSame(0, DB::table('SeasonEventParticipantFinance')->count());
    }

    public function test_payment_above_zero_is_rejected_when_discount_covers_price(): void
    {
        $result = $this->bookings->createBooking(1, $this->guestPayload([
            'first_payment_amount' => 50,
            'discount_amount' => 200,
        ]), 7);

        $this->assertFalse($result['ok']);
        $this->assertSame('first_payment_amount', $result['field']);
        $this->assertSame(0, DB::table('SeasonEventParticipantFinance')->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function guestPayload(array $overrides): array
    {
        return array_merge([
            'booking_type' => 'GUEST',
            'guest_id' => 1,
            'first_payment_date' => '2026-08-20',
            'first_payment_amount' => 200,
            'discount_amount' => 0,
        ], $overrides);
    }

    private function seedEvent(int $price, int $maxInstallments, int $minimumDeposit): void
    {
        DB::table('Season')->insert([
            'SeasonID' => 1,
            'SeasonName' => 'Test',
            'SeasonYear' => 2026,
        ]);
        DB::table('EventType')->insert([
            'EventTypeID' => 1,
            'EventTypeName' => 'Camp',
            'TakesReservation' => 0,
        ]);
        DB::table('Event')->insert([
            'EventID' => 1,
            'EventTypeID' => 1,
            'EventName' => 'Trip',
            'EventStartDate' => '2026-08-20',
            'EventEndDate' => '2026-08-22',
        ]);
        DB::table('SeasonEvent')->insert([
            'SeasonEventID' => 1,
            'SeasonID' => 1,
            'EventID' => 1,
        ]);
        DB::table('SeasonEventFinance')->insert([
            'SeasonEventID' => 1,
            'MaxInstallmentsNumber' => $maxInstallments,
            'MinimumDeposit' => $minimumDeposit,
            'AllowBelowMinimumDeposit' => 0,
            'SendQrWhatsApp' => 0,
        ]);
        DB::table('SeasonEventFinancePrice')->insert([
            'SeasonEventID' => 1,
            'StartDate' => '2026-01-01',
            'EndDate' => '2026-12-31',
            'Price' => $price,
        ]);
        DB::table('Guests')->insert(['GuestID' => 1, 'FirstName' => 'Guest']);
        DB::table('EventQetaa')->insert(['EventID' => 1, 'QetaaID' => 1]);
        DB::table('PersonQetaa')->insert(['PersonID' => 7, 'QetaaID' => 1]);
    }

    private function createSchema(): void
    {
        foreach ([
            'SeasonEventParticipantFinanceReceipt',
            'SeasonEventParticipantFinancePayment',
            'SeasonEventParticipantFinance',
            'SeasonEventFinancePrice',
            'SeasonEventFinance',
            'SeasonEvent',
            'Event',
            'EventType',
            'Season',
            'Guests',
            'EventQetaa',
            'PersonQetaa',
            'PersonBlackList',
            'PersonSpecialCase',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('Season', function (Blueprint $table) {
            $table->increments('SeasonID');
            $table->string('SeasonName')->nullable();
            $table->integer('SeasonYear')->nullable();
        });
        Schema::create('EventType', function (Blueprint $table) {
            $table->increments('EventTypeID');
            $table->string('EventTypeName')->nullable();
            $table->unsignedTinyInteger('TakesReservation')->default(0);
        });
        Schema::create('Event', function (Blueprint $table) {
            $table->increments('EventID');
            $table->unsignedInteger('EventTypeID');
            $table->string('EventName')->nullable();
            $table->string('EventStartDate')->nullable();
            $table->string('EventEndDate')->nullable();
        });
        Schema::create('SeasonEvent', function (Blueprint $table) {
            $table->increments('SeasonEventID');
            $table->unsignedInteger('SeasonID');
            $table->unsignedInteger('EventID');
        });
        Schema::create('SeasonEventFinance', function (Blueprint $table) {
            $table->increments('SeasonEventFinanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedInteger('MaxInstallmentsNumber')->default(1);
            $table->integer('MinimumDeposit')->default(0);
            $table->unsignedTinyInteger('AllowBelowMinimumDeposit')->default(0);
            $table->unsignedTinyInteger('SendQrWhatsApp')->default(0);
        });
        Schema::create('SeasonEventFinancePrice', function (Blueprint $table) {
            $table->increments('SeasonEventFinancePriceID');
            $table->unsignedInteger('SeasonEventID');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->integer('Price');
        });
        Schema::create('Guests', function (Blueprint $table) {
            $table->increments('GuestID');
            $table->string('FirstName')->nullable();
        });
        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->increments('EventQetaaID');
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('PersonBlackList', function (Blueprint $table) {
            $table->increments('PersonBlackListID');
            $table->unsignedInteger('PersonID');
        });
        Schema::create('PersonSpecialCase', function (Blueprint $table) {
            $table->increments('PersonSpecialCaseID');
            $table->unsignedInteger('PersonID');
        });
        Schema::create('SeasonEventParticipantFinance', function (Blueprint $table) {
            $table->increments('SeasonEventParticipantFinanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedInteger('PersonID')->nullable();
            $table->unsignedInteger('GuestID')->nullable();
            $table->unsignedInteger('FamilyID')->nullable();
            $table->unsignedInteger('ServentID')->nullable();
            $table->dateTime('FirstPaymentDate')->nullable();
            $table->integer('OriginalPrice')->default(0);
            $table->integer('DiscountAmount')->default(0);
            $table->integer('FinalRequiredAmount')->default(0);
            $table->string('SpecialCaseType')->nullable();
            $table->string('SpecialCaseNote')->nullable();
            $table->unsignedTinyInteger('HasPersonSpecialCase')->default(0);
            $table->integer('LockedPrice')->default(0);
            $table->unsignedTinyInteger('IsRefunded')->default(0);
            $table->dateTime('RefundDate')->nullable();
            $table->unsignedInteger('InstallmentsNumber')->default(1);
            $table->integer('AmountPaid')->default(0);
            $table->integer('RemainingAmount')->default(0);
            $table->string('ShirtSize')->nullable();
            $table->text('Notes')->nullable();
        });
        Schema::create('SeasonEventParticipantFinancePayment', function (Blueprint $table) {
            $table->increments('PaymentID');
            $table->unsignedInteger('SeasonEventParticipantFinanceID');
            $table->unsignedInteger('ServentID')->nullable();
            $table->dateTime('PaymentDate')->nullable();
            $table->integer('Amount')->default(0);
            $table->unsignedInteger('InstallmentNumber')->default(1);
            $table->string('PaymentType')->default('PAYMENT');
            $table->string('Notes')->nullable();
        });
        Schema::create('SeasonEventParticipantFinanceReceipt', function (Blueprint $table) {
            $table->increments('ReceiptID');
            $table->unsignedInteger('PaymentID');
            $table->string('ReceiptNumber')->nullable();
            $table->dateTime('IssuedAt')->nullable();
            $table->unsignedInteger('IssuedByServentID')->nullable();
        });
    }
}
