<?php

namespace Tests\Unit;

use App\Domain\EventFinance\FinancePlanIntervals;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinancePlanIntervalsTest extends TestCase
{
    private const EVENT_START = '2026-05-10';

    /** @var array<int, string> */
    private const SECTORS = [1 => 'A', 2 => 'B', 3 => 'D'];

    private FinancePlanIntervals $intervals;

    protected function setUp(): void
    {
        parent::setUp();
        $this->intervals = new FinancePlanIntervals;
    }

    public function test_same_dates_with_different_audiences_and_prices_are_accepted(): void
    {
        $result = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-10', 200, ['Q:1', 'Q:2', 'GUEST']),
            $this->row('2026-05-05', '2026-05-10', 400, ['Q:3', 'FAMILY']),
        ], self::SECTORS, self::EVENT_START);

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertCount(2, $result['intervals']);
        $this->assertSame(200, $result['intervals'][0]['Price']);
        $this->assertSame(['Q:1', 'Q:2', 'GUEST'], $result['intervals'][0]['Audience']);
    }

    public function test_rows_are_read_by_key_so_sparse_indices_stay_aligned(): void
    {
        $result = $this->intervals->prepare([
            7 => $this->row('2026-05-01', '2026-05-04', 180, ['Q:1', 'Q:2', 'Q:3']),
            3 => $this->row('2026-05-05', '2026-05-10', 200, ['Q:1', 'Q:2', 'Q:3']),
        ], self::SECTORS, self::EVENT_START);

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertSame(['2026-05-01', '2026-05-05'], array_column($result['intervals'], 'StartDate'));
    }

    public function test_gap_inside_one_sector_chain_is_rejected_with_that_sector_named(): void
    {
        $result = $this->intervals->prepare([
            $this->row('2026-05-01', '2026-05-04', 180, ['Q:1', 'Q:2', 'Q:3']),
            $this->row('2026-05-06', '2026-05-10', 200, ['Q:1']),
            $this->row('2026-05-05', '2026-05-10', 200, ['Q:2', 'Q:3']),
        ], self::SECTORS, self::EVENT_START);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('A', $result['message']);
    }

    public function test_overlapping_rows_for_the_same_audience_are_rejected(): void
    {
        $result = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-10', 200, ['Q:1', 'Q:2']),
            $this->row('2026-05-05', '2026-05-10', 300, ['Q:2', 'Q:3']),
        ], self::SECTORS, self::EVENT_START);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('B', $result['message']);
    }

    public function test_tail_is_auto_filled_per_audience_and_identical_fillers_are_merged(): void
    {
        $result = $this->intervals->prepare([
            $this->row('2026-05-01', '2026-05-05', 200, ['Q:1', 'Q:2']),
            $this->row('2026-05-01', '2026-05-05', 400, ['Q:3']),
            $this->row('2026-05-01', '2026-05-10', 150, ['GUEST']),
        ], self::SECTORS, self::EVENT_START);

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertCount(5, $result['intervals']);

        $fillers = array_values(array_filter($result['intervals'], fn (array $i) => $i['StartDate'] === '2026-05-06'));
        $this->assertCount(2, $fillers);
        $this->assertSame([self::EVENT_START, self::EVENT_START], array_column($fillers, 'EndDate'));
        $this->assertEqualsCanonicalizing([200, 400], array_column($fillers, 'Price'));
        $cheap = array_values(array_filter($fillers, fn (array $i) => $i['Price'] === 200))[0];
        $this->assertEqualsCanonicalizing(['Q:1', 'Q:2'], $cheap['Audience']);
    }

    public function test_every_event_sector_must_be_priced(): void
    {
        $result = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-10', 200, ['Q:1', 'Q:2']),
        ], self::SECTORS, self::EVENT_START);

        $this->assertFalse($result['success']);
        $this->assertSame(__('Sectors without a price: :sectors', ['sectors' => 'D']), $result['message']);
    }

    public function test_families_and_guests_are_optional(): void
    {
        $result = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-10', 200, ['Q:1', 'Q:2', 'Q:3']),
        ], self::SECTORS, self::EVENT_START);

        $this->assertTrue($result['success'], $result['message'] ?? '');
    }

    public function test_empty_or_foreign_audience_is_rejected(): void
    {
        $empty = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-10', 200, []),
        ], self::SECTORS, self::EVENT_START);
        $foreign = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-10', 200, ['Q:1', 'Q:2', 'Q:3', 'Q:99']),
        ], self::SECTORS, self::EVENT_START);
        $garbage = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-10', 200, ['Q:1', 'Q:2', 'Q:3', "1' OR 1=1"]),
        ], self::SECTORS, self::EVENT_START);

        $this->assertSame(__('Each price interval must apply to at least one sector, families, or guests.'), $empty['message']);
        $this->assertSame(__('Price interval audience is invalid.'), $foreign['message']);
        $this->assertSame(__('Price interval audience is invalid.'), $garbage['message']);
    }

    public function test_duplicate_audience_keys_are_collapsed(): void
    {
        $result = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-10', 200, ['Q:1', 'Q:1', 'Q:2', 'Q:3', 'GUEST', 'GUEST']),
        ], self::SECTORS, self::EVENT_START);

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertSame(['Q:1', 'Q:2', 'Q:3', 'GUEST'], $result['intervals'][0]['Audience']);
    }

    public function test_interval_after_event_start_and_too_many_rows_are_rejected(): void
    {
        $late = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-11', 200, ['Q:1', 'Q:2', 'Q:3']),
        ], self::SECTORS, self::EVENT_START);
        $tooMany = $this->intervals->prepare(
            array_fill(0, FinancePlanIntervals::MAX_ROWS + 1, $this->row('2026-05-05', '2026-05-10', 200, ['Q:1'])),
            self::SECTORS,
            self::EVENT_START
        );

        $this->assertSame(__('No price interval may exceed the event start date.'), $late['message']);
        $this->assertSame(__('Too many price intervals (max :max).', ['max' => FinancePlanIntervals::MAX_ROWS]), $tooMany['message']);
    }

    public function test_replace_for_edit_and_delete_round_trip(): void
    {
        $this->createSchema();

        $prepared = $this->intervals->prepare([
            $this->row('2026-05-05', '2026-05-10', 200, ['Q:1', 'Q:2', 'GUEST']),
            $this->row('2026-05-05', '2026-05-10', 400, ['Q:3', 'FAMILY']),
        ], self::SECTORS, self::EVENT_START);
        $this->intervals->replace(1, $prepared['intervals']);

        $this->assertSame(2, DB::table('SeasonEventFinancePrice')->count());
        $this->assertSame(5, DB::table('SeasonEventFinancePriceAudience')->count());
        $this->assertSame(
            ['AudienceType' => 'QETAA', 'QetaaID' => 1],
            (array) DB::table('SeasonEventFinancePriceAudience')->where('QetaaID', 1)->first(['AudienceType', 'QetaaID'])
        );

        $edit = $this->intervals->forEdit(1)->all();
        $this->assertSame('2026-05-05', $edit[0]['start_date']);
        $this->assertSame(200, $edit[0]['price']);
        $this->assertEqualsCanonicalizing(['Q:1', 'Q:2', 'GUEST'], $edit[0]['audience']);
        $this->assertEqualsCanonicalizing(['Q:3', 'FAMILY'], $edit[1]['audience']);

        $this->intervals->replace(1, [$prepared['intervals'][0]]);
        $this->assertSame(1, DB::table('SeasonEventFinancePrice')->count());
        $this->assertSame(3, DB::table('SeasonEventFinancePriceAudience')->count());

        $this->intervals->deleteAll(1);
        $this->assertSame(0, DB::table('SeasonEventFinancePrice')->count());
        $this->assertSame(0, DB::table('SeasonEventFinancePriceAudience')->count());
    }

    public function test_event_sectors_collapse_duplicate_event_qetaa_rows(): void
    {
        $this->createSchema();
        DB::table('SeasonEvent')->insert(['SeasonEventID' => 1, 'SeasonID' => 1, 'EventID' => 5]);
        DB::table('Qetaa')->insert([
            ['QetaaID' => 1, 'QetaaName' => 'Beta'],
            ['QetaaID' => 2, 'QetaaName' => 'Alpha'],
        ]);
        DB::table('EventQetaa')->insert([
            ['EventID' => 5, 'QetaaID' => 1],
            ['EventID' => 5, 'QetaaID' => 1],
            ['EventID' => 5, 'QetaaID' => 2],
            ['EventID' => 6, 'QetaaID' => 2],
        ]);

        $this->assertSame([2 => 'Alpha', 1 => 'Beta'], $this->intervals->eventSectors(1));
        $this->assertSame([], $this->intervals->eventSectors(99));
    }

    /**
     * @param  list<string>  $audience
     * @return array<string, mixed>
     */
    private function row(string $start, string $end, int $price, array $audience): array
    {
        return ['start_date' => $start, 'end_date' => $end, 'price' => $price, 'audience' => $audience];
    }

    private function createSchema(): void
    {
        foreach (['SeasonEventFinancePriceAudience', 'SeasonEventFinancePrice', 'SeasonEvent', 'EventQetaa', 'Qetaa'] as $table) {
            Schema::dropIfExists($table);
        }

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
        Schema::create('SeasonEvent', function (Blueprint $table) {
            $table->increments('SeasonEventID');
            $table->unsignedInteger('SeasonID');
            $table->unsignedInteger('EventID');
        });
        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('Qetaa', function (Blueprint $table) {
            $table->unsignedInteger('QetaaID')->primary();
            $table->string('QetaaName')->nullable();
        });
    }
}
