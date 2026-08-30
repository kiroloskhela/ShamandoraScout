<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomeControllerCalendarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->createSchema();
    }

    public function test_home_calendar_keeps_upcoming_event_and_drops_old_past_event(): void
    {
        $user = User::create([
            'FirstName' => 'Cal',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'CAL'.uniqid(),
        ]);
        $this->grantStaffRole($user);

        DB::table('PersonGroup')->insert(['PersonID' => $user->PersonID, 'GroupID' => 1]);
        DB::table('Qetaa')->insert(['QetaaID' => 1, 'QetaaName' => 'Alpha']);
        DB::table('GroupQetaa')->insert(['GroupID' => 1, 'QetaaID' => 1]);
        DB::table('EventType')->insert(['EventTypeID' => 1, 'EventTypeName' => 'Camp']);
        DB::table('Season')->insert(['SeasonID' => 1, 'SeasonName' => 'S', 'SeasonYear' => '2026']);

        $pastId = DB::table('Event')->insertGetId([
            'EventName' => 'OldCamp',
            'EventStartDate' => now()->subYear()->toDateString(),
            'EventEndDate' => now()->subYear()->addDay()->toDateString(),
            'EventTypeID' => 1,
        ]);
        $upcomingId = DB::table('Event')->insertGetId([
            'EventName' => 'NewCamp',
            'EventStartDate' => now()->addDays(3)->toDateString(),
            'EventEndDate' => now()->addDays(5)->toDateString(),
            'EventTypeID' => 1,
        ]);

        DB::table('EventQetaa')->insert([
            ['EventID' => $pastId, 'QetaaID' => 1],
            ['EventID' => $upcomingId, 'QetaaID' => 1],
        ]);
        DB::table('SeasonEvent')->insert([
            ['EventID' => $pastId, 'SeasonID' => 1],
            ['EventID' => $upcomingId, 'SeasonID' => 1],
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('NewCamp', false)
            ->assertDontSee('OldCamp', false);
    }

    private function createSchema(): void
    {
        foreach ([
            'Season', 'SeasonEvent', 'EventType', 'Event', 'EventQetaa',
            'PersonGroup', 'GroupQetaa', 'Qetaa', 'PersonQetaa',
            'PersonRole', 'Roles', 'PersonImages', 'PersonInformation',
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
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName')->nullable();
        });
        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
        });
        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('Event', function (Blueprint $table) {
            $table->increments('EventID');
            $table->string('EventName')->nullable();
            $table->date('EventStartDate')->nullable();
            $table->date('EventEndDate')->nullable();
            $table->unsignedInteger('EventTypeID')->nullable();
        });
        Schema::create('EventType', function (Blueprint $table) {
            $table->increments('EventTypeID');
            $table->string('EventTypeName')->nullable();
        });
        Schema::create('SeasonEvent', function (Blueprint $table) {
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('SeasonID');
        });
        Schema::create('Season', function (Blueprint $table) {
            $table->increments('SeasonID');
            $table->string('SeasonName')->nullable();
            $table->string('SeasonYear')->nullable();
        });
    }
}
