<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Finance plan create/edit with per-audience price intervals (HTTP layer).
 */
class FinancePlanAudienceTest extends TestCase
{
    private User $finance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->createSchema();
        $this->seedEvent();
        $this->finance = $this->createUserWithRole('Finance');
    }

    public function test_store_saves_one_price_per_audience_set(): void
    {
        $response = $this->actingAs($this->finance)->post(route('finance.store'), $this->planPayload([
            ['start_date' => '2026-05-05', 'end_date' => '2026-05-10', 'price' => 200, 'audience' => ['Q:1', 'Q:2', 'GUEST']],
            ['start_date' => '2026-05-05', 'end_date' => '2026-05-10', 'price' => 400, 'audience' => ['Q:3', 'FAMILY']],
        ]));

        $response->assertRedirect(route('finance.index'))->assertSessionHas('success');
        $this->assertSame(2, DB::table('SeasonEventFinancePrice')->count());
        $this->assertSame(5, DB::table('SeasonEventFinancePriceAudience')->count());

        $expensive = DB::table('SeasonEventFinancePrice')->where('Price', 400)->first();
        $this->assertEqualsCanonicalizing(
            ['QETAA:3', 'FAMILY:'],
            DB::table('SeasonEventFinancePriceAudience')
                ->where('SeasonEventFinancePriceID', $expensive->SeasonEventFinancePriceID)
                ->get()
                ->map(fn ($a) => $a->AudienceType.':'.$a->QetaaID)
                ->all()
        );
    }

    public function test_store_rejects_plan_leaving_a_sector_unpriced(): void
    {
        $response = $this->actingAs($this->finance)->post(route('finance.store'), $this->planPayload([
            ['start_date' => '2026-05-05', 'end_date' => '2026-05-10', 'price' => 200, 'audience' => ['Q:1', 'Q:2']],
        ]));

        $response->assertRedirect()->assertSessionHasErrors('intervals');
        $this->assertStringContainsString('Gamma', session('errors')->first('intervals'));
        $this->assertSame(0, DB::table('SeasonEventFinance')->count());
        $this->assertSame(0, DB::table('SeasonEventFinancePrice')->count());
    }

    public function test_store_rejects_sector_not_linked_to_the_event(): void
    {
        $response = $this->actingAs($this->finance)->post(route('finance.store'), $this->planPayload([
            ['start_date' => '2026-05-05', 'end_date' => '2026-05-10', 'price' => 200, 'audience' => ['Q:1', 'Q:2', 'Q:3', 'Q:99']],
        ]));

        $response->assertRedirect()->assertSessionHasErrors('intervals');
        $this->assertSame(0, DB::table('SeasonEventFinancePrice')->count());
    }

    public function test_events_json_lists_each_events_sectors(): void
    {
        $response = $this->actingAs($this->finance)
            ->getJson(route('finance.getEventsForSeason', ['seasonID' => 1]));

        $response->assertOk()
            ->assertJsonPath('0.SeasonEventID', 1)
            ->assertJsonCount(3, '0.Sectors')
            ->assertJsonPath('0.Sectors.0.QetaaName', 'Alpha');
    }

    public function test_edit_shows_saved_audiences_and_update_replaces_rows(): void
    {
        $this->actingAs($this->finance)->post(route('finance.store'), $this->planPayload([
            ['start_date' => '2026-05-05', 'end_date' => '2026-05-10', 'price' => 200, 'audience' => ['Q:1', 'Q:2', 'Q:3']],
        ]));

        $this->actingAs($this->finance)->get(route('finance.edit', 1))
            ->assertOk()
            ->assertSee('"audience":["Q:1","Q:2","Q:3"]', false)
            ->assertSee('"QetaaName":"Alpha"', false);

        $this->actingAs($this->finance)->post(route('finance.update', 1), $this->planPayload([
            ['start_date' => '2026-05-01', 'end_date' => '2026-05-04', 'price' => 180, 'audience' => ['Q:1', 'Q:2', 'Q:3']],
            ['start_date' => '2026-05-05', 'end_date' => '2026-05-10', 'price' => 200, 'audience' => ['Q:1', 'Q:2']],
            ['start_date' => '2026-05-05', 'end_date' => '2026-05-10', 'price' => 400, 'audience' => ['Q:3']],
        ]))->assertRedirect(route('finance.index'))->assertSessionHas('success');

        $this->assertSame(3, DB::table('SeasonEventFinancePrice')->count());
        $this->assertSame(6, DB::table('SeasonEventFinancePriceAudience')->count());
        $this->assertSame(0, DB::table('SeasonEventFinancePriceAudience')->whereNotIn(
            'SeasonEventFinancePriceID',
            DB::table('SeasonEventFinancePrice')->pluck('SeasonEventFinancePriceID')
        )->count(), 'audience rows of replaced prices must be gone');
    }

    /**
     * @param  list<array<string, mixed>>  $intervals
     * @return array<string, mixed>
     */
    private function planPayload(array $intervals): array
    {
        return [
            'season_id' => 1,
            'season_event_id' => 1,
            'max_installments_number' => 2,
            'minimum_deposit' => 50,
            'allow_below_minimum_deposit' => 1,
            'have_shirt' => 0,
            'send_qr_whatsapp' => 0,
            'intervals' => $intervals,
        ];
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Fin',
            'SecondName' => $roleName,
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'FIN'.uniqid(),
        ]);

        $roleId = (int) DB::table('Roles')->insertGetId(['RoleName' => $roleName]);
        DB::table('PersonRole')->insert(['PersonID' => $user->PersonID, 'RoleID' => $roleId]);

        return $user->fresh();
    }

    private function seedEvent(): void
    {
        DB::table('Season')->insert(['SeasonID' => 1, 'SeasonName' => 'Test', 'SeasonYear' => 2026]);
        DB::table('EventType')->insert(['EventTypeID' => 1, 'EventTypeName' => 'Camp', 'TakesReservation' => 1]);
        DB::table('Event')->insert([
            'EventID' => 1,
            'EventTypeID' => 1,
            'EventName' => 'Trip',
            'EventStartDate' => '2026-05-10',
            'EventEndDate' => '2026-05-12',
        ]);
        DB::table('SeasonEvent')->insert(['SeasonEventID' => 1, 'SeasonID' => 1, 'EventID' => 1]);
        DB::table('Qetaa')->insert([
            ['QetaaID' => 1, 'QetaaName' => 'Alpha'],
            ['QetaaID' => 2, 'QetaaName' => 'Beta'],
            ['QetaaID' => 3, 'QetaaName' => 'Gamma'],
            ['QetaaID' => 99, 'QetaaName' => 'Elsewhere'],
        ]);
        DB::table('EventQetaa')->insert([
            ['EventID' => 1, 'QetaaID' => 1],
            ['EventID' => 1, 'QetaaID' => 1],
            ['EventID' => 1, 'QetaaID' => 2],
            ['EventID' => 1, 'QetaaID' => 3],
        ]);
    }

    private function createSchema(): void
    {
        foreach ([
            'PersonRole', 'Roles', 'PersonImages', 'PersonInformation',
            'SeasonEventParticipantFinancePayment', 'SeasonEventParticipantFinance',
            'SeasonEventFinancePriceAudience', 'SeasonEventFinancePrice', 'SeasonEventFinance',
            'SeasonEvent', 'Event', 'EventType', 'Season', 'Qetaa', 'EventQetaa',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
        });
        Schema::create('PersonImages', function (Blueprint $table) {
            $table->increments('PersonImageID');
            $table->unsignedInteger('PersonID')->nullable();
            $table->string('PersonSystemImagePath')->nullable();
            $table->string('PersonSystemImageThumbnailPath')->nullable();
        });
        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('RoleID');
            $table->string('RoleName')->nullable();
        });
        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
        });
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
        Schema::create('Qetaa', function (Blueprint $table) {
            $table->unsignedInteger('QetaaID')->primary();
            $table->string('QetaaName')->nullable();
        });
        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('SeasonEventFinance', function (Blueprint $table) {
            $table->unsignedInteger('SeasonEventID')->primary();
            $table->unsignedInteger('MaxInstallmentsNumber')->default(1);
            $table->integer('MinimumDeposit')->default(0);
            $table->unsignedTinyInteger('AllowBelowMinimumDeposit')->default(1);
            $table->unsignedTinyInteger('HaveShirt')->default(0);
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
        Schema::create('SeasonEventParticipantFinance', function (Blueprint $table) {
            $table->increments('SeasonEventParticipantFinanceID');
            $table->unsignedInteger('SeasonEventID');
        });
        Schema::create('SeasonEventParticipantFinancePayment', function (Blueprint $table) {
            $table->increments('PaymentID');
            $table->unsignedInteger('SeasonEventParticipantFinanceID');
        });
    }
}
