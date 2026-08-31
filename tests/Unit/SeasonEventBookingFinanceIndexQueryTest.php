<?php

namespace Tests\Unit;

use App\Domain\EventFinance\SeasonEventBookingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeasonEventBookingFinanceIndexQueryTest extends TestCase
{
    private const EVENT_A = 87;

    private const EVENT_B = 1;

    private SeasonEventBookingService $bookings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookings = new SeasonEventBookingService;
        $this->createSchema();
    }

    public function test_list_is_one_row_per_booking_and_ignores_other_events(): void
    {
        $personId = $this->insertPerson(10, 'Maged', 'Nabil');
        $unpaidId = $this->insertBooking(self::EVENT_A, [
            'PersonID' => $personId,
            'FinalRequiredAmount' => 200,
            'RemainingAmount' => 200,
        ]);
        $paidId = $this->insertBooking(self::EVENT_A, [
            'PersonID' => $personId,
            'AmountPaid' => 50,
            'RemainingAmount' => 150,
            'InstallmentsNumber' => 2,
        ]);
        $otherId = $this->insertBooking(self::EVENT_B, [
            'PersonID' => $personId,
            'AmountPaid' => 999,
        ]);

        DB::table('PersonPhoneNumbers')->insert([
            ['PersonID' => $personId, 'PersonPersonalMobileNumber' => '01200000002'],
            ['PersonID' => $personId, 'PersonPersonalMobileNumber' => '01200000001'],
        ]);
        DB::table('Qetaa')->insert([
            ['QetaaID' => 1, 'QetaaName' => 'Beta'],
            ['QetaaID' => 2, 'QetaaName' => 'Alpha'],
        ]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $personId, 'QetaaID' => 1],
            ['PersonID' => $personId, 'QetaaID' => 2],
        ]);

        $this->insertPayment($paidId, 'PAYMENT', '2026-09-01 10:00:00', 50);
        $this->insertPayment($otherId, 'PAYMENT', '2026-09-01 10:00:00', 999);

        $rows = $this->bookings->listFinanceIndexBookings(self::EVENT_A);

        $this->assertCount(2, $rows);
        $this->assertSame([$unpaidId, $paidId], $rows->pluck('SeasonEventParticipantFinanceID')->sort()->values()->all());

        $unpaid = $rows->firstWhere('SeasonEventParticipantFinanceID', $unpaidId);
        $this->assertSame(0, (int) $unpaid->PaymentsCount);
        $this->assertNull($unpaid->LastPaymentID);
        $this->assertSame('-', $unpaid->FirstPaymentDateFormatted);

        $paid = $rows->firstWhere('SeasonEventParticipantFinanceID', $paidId);
        $this->assertSame(1, (int) $paid->PaymentsCount);
        $this->assertSame('01200000001', $paid->PersonPersonalMobileNumber);
        $this->assertStringContainsString('Alpha', (string) $paid->QetaaNames);
        $this->assertStringContainsString('Beta', (string) $paid->QetaaNames);

        $summaries = $this->bookings->getFinanceIndexSummaries(self::EVENT_A, '2026-09-01');
        $this->assertSame(2, $summaries['total']['people_count']);
        $this->assertSame(50.0, $summaries['total']['payments_amount']);
        $this->assertSame(50.0, $summaries['selected_day']['payments_amount']);
        $this->assertSame(0.0, $summaries['total']['refund_amount']);
    }

    public function test_last_payment_id_follows_date_then_id_like_is_last_payment(): void
    {
        $bookingId = $this->insertBooking(self::EVENT_A, ['AmountPaid' => 30]);
        $newerLowerId = $this->insertPayment($bookingId, 'PAYMENT', '2026-02-01 10:00:00', 10);
        $olderHigherId = $this->insertPayment($bookingId, 'PAYMENT', '2026-01-01 10:00:00', 10);

        $this->assertGreaterThan($newerLowerId, $olderHigherId);

        $row = $this->bookings->listFinanceIndexBookings(self::EVENT_A)->first();
        $this->assertSame($newerLowerId, (int) $row->LastPaymentID);
        $this->assertTrue($this->bookings->isLastPayment($bookingId, (int) $row->LastPaymentID));
        $this->assertSame(2, (int) $row->PaymentsCount);

        $tieBooking = $this->insertBooking(self::EVENT_A, ['AmountPaid' => 20]);
        $firstSameDay = $this->insertPayment($tieBooking, 'PAYMENT', '2026-03-01 12:00:00', 10);
        $secondSameDay = $this->insertPayment($tieBooking, 'PAYMENT', '2026-03-01 12:00:00', 10);

        $tie = $this->bookings->listFinanceIndexBookings(self::EVENT_A)
            ->firstWhere('SeasonEventParticipantFinanceID', $tieBooking);
        $this->assertSame($secondSameDay, (int) $tie->LastPaymentID);
        $this->assertTrue($this->bookings->isLastPayment($tieBooking, $secondSameDay));
        $this->assertNotSame($firstSameDay, (int) $tie->LastPaymentID);
    }

    public function test_refunds_are_excluded_from_payment_count_and_included_in_last_payment(): void
    {
        $bookingId = $this->insertBooking(self::EVENT_A, [
            'AmountPaid' => 40,
            'IsRefunded' => 1,
            'FirstPaymentDate' => '2026-09-01 09:00:00',
        ]);
        $paymentId = $this->insertPayment($bookingId, 'PAYMENT', '2026-09-01 09:00:00', 80);
        $refundId = $this->insertPayment($bookingId, 'REFUND', '2026-09-02 11:00:00', 40);

        $guestId = DB::table('Guests')->insertGetId([
            'FirstName' => 'Guest',
            'MobileNumber' => '01000000000',
        ]);
        $guestBooking = $this->insertBooking(self::EVENT_A, [
            'GuestID' => $guestId,
            'FirstPaymentDate' => '2026-09-01 08:00:00',
        ]);

        $lonelyPerson = $this->insertPerson(11, 'No', 'Sector');
        $this->insertBooking(self::EVENT_A, ['PersonID' => $lonelyPerson]);

        $row = $this->bookings->listFinanceIndexBookings(self::EVENT_A)
            ->firstWhere('SeasonEventParticipantFinanceID', $bookingId);

        $this->assertSame(1, (int) $row->PaymentsCount);
        $this->assertSame($refundId, (int) $row->LastPaymentID);
        $this->assertTrue($this->bookings->isLastPayment($bookingId, $refundId));
        $this->assertFalse($this->bookings->isLastPayment($bookingId, $paymentId));
        $this->assertStringContainsString('مسترد', $row->BookingStatusText);

        $guest = $this->bookings->listFinanceIndexBookings(self::EVENT_A)
            ->firstWhere('SeasonEventParticipantFinanceID', $guestBooking);
        $this->assertSame('ضيوف', $guest->QetaaNames);
        $this->assertSame('01000000000', $guest->PersonPersonalMobileNumber);

        $noSector = $this->bookings->listFinanceIndexBookings(self::EVENT_A)
            ->firstWhere('PersonID', $lonelyPerson);
        $this->assertSame('-', $noSector->QetaaNames);
        $this->assertSame('-', $noSector->PersonPersonalMobileNumber);

        $summaries = $this->bookings->getFinanceIndexSummaries(self::EVENT_A, '2026-09-01');
        $this->assertSame(80.0, $summaries['total']['payments_amount']);
        $this->assertSame(40.0, $summaries['total']['refund_amount']);
        $this->assertSame(80.0, $summaries['selected_day']['payments_amount']);
        $this->assertSame(0.0, $summaries['selected_day']['refund_amount']);
        $this->assertSame(2, $summaries['selected_day']['people_count']);
        $this->assertSame(3, $summaries['total']['people_count']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertBooking(int $seasonEventId, array $overrides = []): int
    {
        return (int) DB::table('SeasonEventParticipantFinance')->insertGetId(array_merge([
            'SeasonEventID' => $seasonEventId,
            'PersonID' => null,
            'GuestID' => null,
            'FamilyID' => null,
            'ServentID' => null,
            'FirstPaymentDate' => null,
            'OriginalPrice' => 200,
            'DiscountAmount' => 0,
            'FinalRequiredAmount' => 200,
            'SpecialCaseType' => null,
            'SpecialCaseNote' => null,
            'HasPersonSpecialCase' => 0,
            'IsRefunded' => 0,
            'InstallmentsNumber' => 1,
            'AmountPaid' => 0,
            'RemainingAmount' => 200,
            'ShirtSize' => null,
        ], $overrides));
    }

    private function insertPayment(int $bookingId, string $type, string $date, int $amount): int
    {
        return (int) DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
            'SeasonEventParticipantFinanceID' => $bookingId,
            'PaymentType' => $type,
            'PaymentDate' => $date,
            'Amount' => $amount,
        ]);
    }

    private function insertPerson(int $personId, string $first, string $second): int
    {
        DB::table('PersonInformation')->insert([
            'PersonID' => $personId,
            'FirstName' => $first,
            'SecondName' => $second,
            'ThirdName' => '',
            'FourthName' => '',
        ]);

        return $personId;
    }

    private function createSchema(): void
    {
        foreach ([
            'SeasonEventParticipantFinancePayment',
            'SeasonEventParticipantFinance',
            'PersonPhoneNumbers',
            'PersonQetaa',
            'Qetaa',
            'PersonInformation',
            'Guests',
            'FamilyMembers',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
        });
        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->string('PersonPersonalMobileNumber')->nullable();
        });
        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName')->nullable();
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('Guests', function (Blueprint $table) {
            $table->increments('GuestID');
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
            $table->string('MobileNumber')->nullable();
        });
        Schema::create('FamilyMembers', function (Blueprint $table) {
            $table->increments('FamilyID');
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
            $table->string('MobileNumber')->nullable();
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
            $table->unsignedTinyInteger('IsRefunded')->default(0);
            $table->unsignedInteger('InstallmentsNumber')->default(1);
            $table->integer('AmountPaid')->default(0);
            $table->integer('RemainingAmount')->default(0);
            $table->string('ShirtSize')->nullable();
        });
        Schema::create('SeasonEventParticipantFinancePayment', function (Blueprint $table) {
            $table->increments('PaymentID');
            $table->unsignedInteger('SeasonEventParticipantFinanceID');
            $table->string('PaymentType')->default('PAYMENT');
            $table->dateTime('PaymentDate')->nullable();
            $table->integer('Amount')->default(0);
        });
    }
}
