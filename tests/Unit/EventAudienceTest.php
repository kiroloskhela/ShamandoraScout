<?php

namespace Tests\Unit;

use App\Domain\Enrolment\LiveFormQetaaResolver;
use App\Domain\Event\EventAudience;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EventAudienceTest extends TestCase
{
    private EventAudience $audience;

    protected function setUp(): void
    {
        parent::setUp();
        $this->audience = new EventAudience;
        $this->createSchema();
    }

    public function test_serving_choice_keeps_only_people_linked_to_a_selected_sector(): void
    {
        $this->seedPeople();
        DB::table('Event')->insert(['EventID' => 1, 'EventName' => 'Camp']);
        DB::table('EventQetaa')->insert([
            ['EventID' => 1, 'QetaaID' => LiveFormQetaaResolver::QETAA_ASHBAL, 'AudienceScope' => EventAudience::ALL],
            ['EventID' => 1, 'QetaaID' => LiveFormQetaaResolver::QETAA_KASHAFA, 'AudienceScope' => EventAudience::ALL],
            ['EventID' => 1, 'QetaaID' => LiveFormQetaaResolver::QETAA_QADA, 'AudienceScope' => EventAudience::SERVING],
            ['EventID' => 1, 'QetaaID' => LiveFormQetaaResolver::QETAA_JAWWALA, 'AudienceScope' => EventAudience::SERVING],
        ]);

        $this->assertTrue($this->audience->personIsEligible(1, 1));
        $this->assertFalse($this->audience->personIsEligible(1, 2));
        $this->assertFalse($this->audience->personIsEligible(1, 3));
        $this->assertTrue($this->audience->personIsEligible(1, 4));
        $this->assertTrue($this->audience->personIsEligible(1, 5));
    }

    public function test_all_choice_includes_every_person_in_that_sector(): void
    {
        $this->seedPeople();
        DB::table('Event')->insert(['EventID' => 2, 'EventName' => 'Leaders']);
        DB::table('EventQetaa')->insert([
            ['EventID' => 2, 'QetaaID' => LiveFormQetaaResolver::QETAA_QADA, 'AudienceScope' => EventAudience::ALL],
        ]);

        $this->assertTrue($this->audience->personIsEligible(2, 1));
        $this->assertTrue($this->audience->personIsEligible(2, 2));
        $this->assertTrue($this->audience->personIsEligible(2, 3));
        $this->assertFalse($this->audience->personIsEligible(2, 4));
    }

    public function test_visibility_follows_the_same_audience(): void
    {
        $this->seedPeople();
        DB::table('Event')->insert([
            ['EventID' => 1, 'EventName' => 'Scoped'],
            ['EventID' => 2, 'EventName' => 'AllLeaders'],
            ['EventID' => 3, 'EventName' => 'AshbalOnly'],
        ]);
        DB::table('EventQetaa')->insert([
            ['EventID' => 1, 'QetaaID' => LiveFormQetaaResolver::QETAA_ASHBAL, 'AudienceScope' => EventAudience::ALL],
            ['EventID' => 1, 'QetaaID' => LiveFormQetaaResolver::QETAA_QADA, 'AudienceScope' => EventAudience::SERVING],
            ['EventID' => 2, 'QetaaID' => LiveFormQetaaResolver::QETAA_QADA, 'AudienceScope' => EventAudience::ALL],
            ['EventID' => 3, 'QetaaID' => LiveFormQetaaResolver::QETAA_ASHBAL, 'AudienceScope' => EventAudience::ALL],
        ]);

        $this->assertSame([1, 2, 3], $this->visibleEventIds(1));
        $this->assertSame([2], $this->visibleEventIds(2));
        $this->assertSame([2], $this->visibleEventIds(3));
        $this->assertEqualsCanonicalizing([1, 3], $this->visibleEventIds(4));
    }

    public function test_serving_choice_is_rejected_until_a_sector_is_selected(): void
    {
        $this->assertTrue($this->audience->hasServingWithoutHost([], [
            LiveFormQetaaResolver::QETAA_QADA => EventAudience::SERVING,
        ]));
        $this->assertSame([], $this->audience->rowsFromInput([], [
            LiveFormQetaaResolver::QETAA_QADA => EventAudience::SERVING,
        ]));

        $rows = $this->audience->rowsFromInput([LiveFormQetaaResolver::QETAA_ASHBAL], [
            LiveFormQetaaResolver::QETAA_QADA => EventAudience::SERVING,
            LiveFormQetaaResolver::QETAA_EADAD_QADA => EventAudience::ALL,
        ]);

        $this->assertSame([
            ['QetaaID' => LiveFormQetaaResolver::QETAA_ASHBAL, 'AudienceScope' => EventAudience::ALL],
            ['QetaaID' => LiveFormQetaaResolver::QETAA_QADA, 'AudienceScope' => EventAudience::SERVING],
            ['QetaaID' => LiveFormQetaaResolver::QETAA_EADAD_QADA, 'AudienceScope' => EventAudience::ALL],
        ], $rows);
    }

    /**
     * @return list<int>
     */
    private function visibleEventIds(int $personId): array
    {
        return DB::table('Event as e')
            ->where(function ($query) use ($personId) {
                $this->audience->whereVisibleToPerson($query, $personId, 'e.EventID');
            })
            ->orderBy('e.EventID')
            ->pluck('e.EventID')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function seedPeople(): void
    {
        DB::table('PersonQetaa')->insert([
            ['PersonID' => 1, 'QetaaID' => LiveFormQetaaResolver::QETAA_QADA],
            ['PersonID' => 2, 'QetaaID' => LiveFormQetaaResolver::QETAA_QADA],
            ['PersonID' => 3, 'QetaaID' => LiveFormQetaaResolver::QETAA_QADA],
            ['PersonID' => 4, 'QetaaID' => LiveFormQetaaResolver::QETAA_ASHBAL],
            ['PersonID' => 5, 'QetaaID' => LiveFormQetaaResolver::QETAA_JAWWALA],
        ]);
        DB::table('GroupQetaa')->insert([
            ['GroupID' => 10, 'QetaaID' => LiveFormQetaaResolver::QETAA_ASHBAL],
            ['GroupID' => 11, 'QetaaID' => LiveFormQetaaResolver::QETAA_ZAHRAT],
            ['GroupID' => 12, 'QetaaID' => LiveFormQetaaResolver::QETAA_KASHAFA],
        ]);
        DB::table('PersonGroup')->insert([
            ['PersonID' => 1, 'GroupID' => 10],
            ['PersonID' => 2, 'GroupID' => 11],
            ['PersonID' => 4, 'GroupID' => 10],
            ['PersonID' => 5, 'GroupID' => 12],
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('EventQetaa');
        Schema::dropIfExists('Event');
        Schema::dropIfExists('PersonGroup');
        Schema::dropIfExists('GroupQetaa');
        Schema::dropIfExists('PersonQetaa');

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
        });
        Schema::create('Event', function (Blueprint $table) {
            $table->unsignedInteger('EventID')->primary();
            $table->string('EventName')->nullable();
        });
        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
            $table->string('AudienceScope', 16)->default('all');
        });
    }
}
