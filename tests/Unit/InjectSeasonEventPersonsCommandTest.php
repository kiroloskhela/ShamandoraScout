<?php

namespace Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InjectSeasonEventPersonsCommandTest extends TestCase
{
    private const FROM = 87;

    private const TO = 93;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
        $this->seedEvents();
    }

    public function test_dry_run_does_not_write(): void
    {
        $this->seedBookings();
        $before = (int) DB::table('SeasonEventParticipantFinance')->count();

        $this->artisan('event-finance:inject-persons', [
            'fromSeasonEventId' => self::FROM,
            'toSeasonEventId' => self::TO,
        ])->assertSuccessful();

        $this->artisan('event-finance:inject-persons', [
            'fromSeasonEventId' => self::FROM,
            'toSeasonEventId' => self::TO,
            '--execute' => true,
            '--dry-run' => true,
            '--servent-id' => 1,
        ])->assertSuccessful();

        $this->assertSame($before, (int) DB::table('SeasonEventParticipantFinance')->count());
    }

    public function test_execute_injects_missing_persons_at_zero_and_skips_overlap(): void
    {
        $this->seedBookings();
        $sourceCount = (int) DB::table('SeasonEventParticipantFinance')->where('SeasonEventID', self::FROM)->count();
        $targetPaidAmount = (int) DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', self::TO)
            ->where('PersonID', 12)
            ->value('AmountPaid');
        $paymentsBefore = (int) DB::table('SeasonEventParticipantFinancePayment')->count();

        $this->artisan('event-finance:inject-persons', [
            'fromSeasonEventId' => self::FROM,
            'toSeasonEventId' => self::TO,
            '--execute' => true,
            '--servent-id' => 1,
        ])->assertSuccessful();

        $this->assertSame(
            $sourceCount,
            (int) DB::table('SeasonEventParticipantFinance')->where('SeasonEventID', self::FROM)->count()
        );
        $this->assertSame(
            $targetPaidAmount,
            (int) DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventID', self::TO)
                ->where('PersonID', 12)
                ->value('AmountPaid')
        );
        $this->assertSame($paymentsBefore, (int) DB::table('SeasonEventParticipantFinancePayment')->count());

        $injected10 = DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', self::TO)
            ->where('PersonID', 10)
            ->first();
        $this->assertNotNull($injected10);
        $this->assertSame(0, (int) $injected10->OriginalPrice);
        $this->assertSame(0, (int) $injected10->DiscountAmount);
        $this->assertSame(0, (int) $injected10->FinalRequiredAmount);
        $this->assertSame(0, (int) $injected10->LockedPrice);
        $this->assertSame(0, (int) $injected10->AmountPaid);
        $this->assertSame(0, (int) $injected10->RemainingAmount);
        $this->assertSame(0, (int) $injected10->IsRefunded);
        $this->assertSame('NONE', $injected10->SpecialCaseType);
        $this->assertSame('Injected from SeasonEvent 87 for attendance', $injected10->Notes);
        $this->assertNull($injected10->GuestID);
        $this->assertNull($injected10->FamilyID);

        $this->assertNotNull(
            DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventID', self::TO)
                ->where('PersonID', 11)
                ->first()
        );

        $this->assertSame(
            1,
            (int) DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventID', self::TO)
                ->where('PersonID', 12)
                ->count()
        );
        $this->assertSame(
            1,
            (int) DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventID', self::TO)
                ->where('PersonID', 14)
                ->where('IsRefunded', 1)
                ->count()
        );
        $this->assertSame(
            0,
            (int) DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventID', self::TO)
                ->whereNotNull('GuestID')
                ->count()
        );

        $this->artisan('event-finance:inject-persons', [
            'fromSeasonEventId' => self::FROM,
            'toSeasonEventId' => self::TO,
            '--execute' => true,
            '--servent-id' => 1,
        ])->assertSuccessful();

        $this->assertSame(
            4,
            (int) DB::table('SeasonEventParticipantFinance')->where('SeasonEventID', self::TO)->count()
        );
    }

    public function test_execute_requires_servent_id(): void
    {
        $this->seedBookings();

        $this->artisan('event-finance:inject-persons', [
            'fromSeasonEventId' => self::FROM,
            'toSeasonEventId' => self::TO,
            '--execute' => true,
        ])->assertFailed();
    }

    public function test_same_source_and_target_is_rejected(): void
    {
        $this->artisan('event-finance:inject-persons', [
            'fromSeasonEventId' => self::FROM,
            'toSeasonEventId' => self::FROM,
        ])->assertFailed();
    }

    private function seedEvents(): void
    {
        DB::table('Season')->insert(['SeasonID' => 1, 'SeasonName' => 'Test', 'SeasonYear' => 2026]);
        DB::table('EventType')->insert([
            'EventTypeID' => 1,
            'EventTypeName' => 'Camp',
            'TakesReservation' => 1,
        ]);
        DB::table('Event')->insert([
            ['EventID' => 1, 'EventTypeID' => 1, 'EventName' => 'Source Camp'],
            ['EventID' => 2, 'EventTypeID' => 1, 'EventName' => 'Target Camp'],
        ]);
        DB::table('SeasonEvent')->insert([
            ['SeasonEventID' => self::FROM, 'SeasonID' => 1, 'EventID' => 1],
            ['SeasonEventID' => self::TO, 'SeasonID' => 1, 'EventID' => 2],
        ]);
        DB::table('PersonInformation')->insert([
            ['PersonID' => 1, 'FirstName' => 'Servent'],
            ['PersonID' => 10, 'FirstName' => 'Missing'],
            ['PersonID' => 11, 'FirstName' => 'RefundedSource'],
            ['PersonID' => 12, 'FirstName' => 'Overlap'],
            ['PersonID' => 14, 'FirstName' => 'RefundedTarget'],
        ]);
    }

    private function seedBookings(): void
    {
        $this->insertBooking(self::FROM, ['PersonID' => 10, 'OriginalPrice' => 200, 'FinalRequiredAmount' => 200, 'RemainingAmount' => 200]);
        $this->insertBooking(self::FROM, ['PersonID' => 11, 'IsRefunded' => 1]);
        $this->insertBooking(self::FROM, ['PersonID' => 12, 'OriginalPrice' => 100]);
        $this->insertBooking(self::FROM, ['PersonID' => 14]);
        $this->insertBooking(self::FROM, ['GuestID' => 1, 'OriginalPrice' => 50]);
        $this->insertBooking(self::FROM, ['FamilyID' => 1, 'OriginalPrice' => 50]);

        $paidId = $this->insertBooking(self::TO, [
            'PersonID' => 12,
            'OriginalPrice' => 500,
            'FinalRequiredAmount' => 500,
            'AmountPaid' => 500,
            'LockedPrice' => 500,
        ]);
        $this->insertBooking(self::TO, ['PersonID' => 14, 'IsRefunded' => 1]);
        DB::table('SeasonEventParticipantFinancePayment')->insert([
            'SeasonEventParticipantFinanceID' => $paidId,
            'Amount' => 500,
            'PaymentType' => 'PAYMENT',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function insertBooking(int $seasonEventId, array $overrides): int
    {
        return (int) DB::table('SeasonEventParticipantFinance')->insertGetId(array_merge([
            'SeasonEventID' => $seasonEventId,
            'PersonID' => null,
            'GuestID' => null,
            'FamilyID' => null,
            'ServentID' => 1,
            'OriginalPrice' => 0,
            'DiscountAmount' => 0,
            'FinalRequiredAmount' => 0,
            'LockedPrice' => 0,
            'IsRefunded' => 0,
            'InstallmentsNumber' => 1,
            'AmountPaid' => 0,
            'RemainingAmount' => 0,
        ], $overrides));
    }

    private function createSchema(): void
    {
        foreach ([
            'SeasonEventParticipantFinancePayment',
            'SeasonEventParticipantFinance',
            'SeasonEvent',
            'Event',
            'EventType',
            'Season',
            'PersonInformation',
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
        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('FirstName')->nullable();
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
            $table->unique(['SeasonEventID', 'PersonID']);
        });
        Schema::create('SeasonEventParticipantFinancePayment', function (Blueprint $table) {
            $table->increments('PaymentID');
            $table->unsignedInteger('SeasonEventParticipantFinanceID');
            $table->integer('Amount')->default(0);
            $table->string('PaymentType')->default('PAYMENT');
        });
    }
}
