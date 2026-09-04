<?php

namespace Tests\Unit;

use App\Domain\EventFinance\SeasonEventBookingService;
use App\Domain\EventFinance\SeasonEventPriceResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Booking price depends on (payment date × who books): sector, family or guest.
 */
class SeasonEventAudiencePricingTest extends TestCase
{
    private const SECTOR_A = 1;

    private const SECTOR_D = 2;

    private const SECTOR_UNPRICED = 3;

    private SeasonEventBookingService $bookings;

    private SeasonEventPriceResolver $prices;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prices = new SeasonEventPriceResolver;
        $this->bookings = new SeasonEventBookingService($this->prices);
        $this->createSchema();
        $this->seedEvent();
    }

    public function test_person_price_follows_their_sector(): void
    {
        $this->seedPerson(10, [self::SECTOR_A]);
        $this->seedPerson(20, [self::SECTOR_D]);

        $a = $this->bookings->createBooking(1, $this->personPayload(10, 200), 7);
        $d = $this->bookings->createBooking(1, $this->personPayload(20, 400), 7);

        $this->assertTrue($a['ok'], $a['message'] ?? '');
        $this->assertTrue($d['ok'], $d['message'] ?? '');
        $this->assertSame(200, (int) DB::table('SeasonEventParticipantFinance')->where('PersonID', 10)->value('OriginalPrice'));
        $this->assertSame(400, (int) DB::table('SeasonEventParticipantFinance')->where('PersonID', 20)->value('OriginalPrice'));
    }

    public function test_guest_uses_guest_price_and_family_without_price_is_refused(): void
    {
        $guest = $this->bookings->createBooking(1, [
            'booking_type' => 'GUEST',
            'guest_id' => 1,
            'first_payment_date' => '2026-05-06',
            'first_payment_amount' => 150,
        ], 7);
        $family = $this->bookings->createBooking(1, [
            'booking_type' => 'FAMILY',
            'family_id' => 1,
            'first_payment_date' => '2026-05-06',
            'first_payment_amount' => 150,
        ], 7);

        $this->assertTrue($guest['ok'], $guest['message'] ?? '');
        $this->assertSame(150, (int) DB::table('SeasonEventParticipantFinance')->where('GuestID', 1)->value('OriginalPrice'));
        $this->assertFalse($family['ok']);
        $this->assertSame('first_payment_date', $family['field']);
    }

    public function test_price_row_without_audience_never_applies(): void
    {
        $this->seedPerson(30, [self::SECTOR_UNPRICED]);

        $result = $this->bookings->createBooking(1, $this->personPayload(30, 100), 7);

        $this->assertFalse($result['ok']);
        $this->assertSame('first_payment_date', $result['field']);
        $this->assertSame(0, DB::table('SeasonEventParticipantFinance')->count());
    }

    public function test_person_in_two_priced_sectors_gets_the_cheapest(): void
    {
        $this->seedPerson(40, [self::SECTOR_A, self::SECTOR_D]);

        $this->assertSame(200, $this->prices->personPrice(1, '2026-05-06', 40));
    }

    public function test_date_outside_every_interval_has_no_price(): void
    {
        $this->seedPerson(10, [self::SECTOR_A]);

        $this->assertNull($this->prices->personPrice(1, '2026-01-01', 10));
        $this->assertNull($this->prices->audiencePrice(1, '2026-01-01', 'GUEST'));
    }

    public function test_audience_intervals_lists_only_that_audience(): void
    {
        $rows = $this->prices->audienceIntervals(1, 'GUEST');

        $this->assertCount(1, $rows);
        $this->assertSame(150, $rows->first()->Price);
        $this->assertCount(0, $this->prices->audienceIntervals(1, 'FAMILY'));
    }

    /**
     * @return array<string, mixed>
     */
    private function personPayload(int $personId, int $amount): array
    {
        return [
            'booking_type' => 'PERSON',
            'person_id' => $personId,
            'first_payment_date' => '2026-05-06',
            'first_payment_amount' => $amount,
        ];
    }

    /**
     * @param  list<int>  $sectorIds
     */
    private function seedPerson(int $personId, array $sectorIds): void
    {
        foreach ($sectorIds as $sectorId) {
            DB::table('PersonQetaa')->insert(['PersonID' => $personId, 'QetaaID' => $sectorId]);
        }
    }

    private function seedEvent(): void
    {
        DB::table('Season')->insert(['SeasonID' => 1, 'SeasonName' => 'Test', 'SeasonYear' => 2026]);
        DB::table('EventType')->insert(['EventTypeID' => 1, 'EventTypeName' => 'Camp', 'TakesReservation' => 0]);
        DB::table('Event')->insert([
            'EventID' => 1,
            'EventTypeID' => 1,
            'EventName' => 'Trip',
            'EventStartDate' => '2026-05-10',
            'EventEndDate' => '2026-05-12',
        ]);
        DB::table('SeasonEvent')->insert(['SeasonEventID' => 1, 'SeasonID' => 1, 'EventID' => 1]);
        DB::table('SeasonEventFinance')->insert([
            'SeasonEventID' => 1,
            'MaxInstallmentsNumber' => 1,
            'MinimumDeposit' => 0,
            'AllowBelowMinimumDeposit' => 1,
            'SendQrWhatsApp' => 0,
        ]);
        foreach ([self::SECTOR_A, self::SECTOR_D, self::SECTOR_UNPRICED] as $sectorId) {
            DB::table('EventQetaa')->insert(['EventID' => 1, 'QetaaID' => $sectorId]);
        }

        $this->seedPrice(200, [['QETAA', self::SECTOR_A]]);
        $this->seedPrice(400, [['QETAA', self::SECTOR_D]]);
        $this->seedPrice(150, [['GUEST', null]]);
        $this->seedPrice(50, []);

        DB::table('Guests')->insert(['GuestID' => 1, 'FirstName' => 'Guest']);
        DB::table('FamilyMembers')->insert(['FamilyID' => 1, 'FirstName' => 'Family']);
    }

    /**
     * @param  list<array{0: string, 1: int|null}>  $audiences
     */
    private function seedPrice(int $price, array $audiences): void
    {
        $priceId = DB::table('SeasonEventFinancePrice')->insertGetId([
            'SeasonEventID' => 1,
            'StartDate' => '2026-05-05',
            'EndDate' => '2026-05-10',
            'Price' => $price,
        ]);

        foreach ($audiences as [$type, $qetaaId]) {
            DB::table('SeasonEventFinancePriceAudience')->insert([
                'SeasonEventFinancePriceID' => $priceId,
                'AudienceType' => $type,
                'QetaaID' => $qetaaId,
            ]);
        }
    }

    private function createSchema(): void
    {
        foreach ([
            'SeasonEventParticipantFinanceReceipt',
            'SeasonEventParticipantFinancePayment',
            'SeasonEventParticipantFinance',
            'SeasonEventFinancePriceAudience',
            'SeasonEventFinancePrice',
            'SeasonEventFinance',
            'SeasonEvent',
            'Event',
            'EventType',
            'Season',
            'Guests',
            'FamilyMembers',
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
        Schema::create('SeasonEventFinancePriceAudience', function (Blueprint $table) {
            $table->increments('SeasonEventFinancePriceAudienceID');
            $table->integer('SeasonEventFinancePriceID');
            $table->string('AudienceType', 10);
            $table->integer('QetaaID')->nullable();
        });
        Schema::create('Guests', function (Blueprint $table) {
            $table->increments('GuestID');
            $table->string('FirstName')->nullable();
        });
        Schema::create('FamilyMembers', function (Blueprint $table) {
            $table->increments('FamilyID');
            $table->string('FirstName')->nullable();
        });
        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
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
