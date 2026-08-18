<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReservationAttendanceApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    public function test_persons_list_is_booked_people_only(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $event = $this->seedReservationEventWithPersonBooking();
        $unbookedPersonId = $this->seedUnbookedPersonInEventSector();

        $response = $this->api($admin)
            ->getJson('/api/attendance/persons?season_event_id='.$event['season_event_id']);

        $response->assertOk()->assertJsonPath('ok', true);
        $ids = collect($response->json('persons'))->pluck('PersonID')->all();
        $this->assertSame([$event['person_id']], $ids);
        $this->assertNotContains($unbookedPersonId, $ids);
        $this->assertSame('absent', $response->json('persons.0.Status'));
    }

    public function test_outside_status_is_returned_as_excused(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $event = $this->seedReservationEventWithPersonBooking();

        DB::table('SeasonEventBookingAttendance')->insert([
            'SeasonEventParticipantFinanceID' => $event['booking_id'],
            'SeasonEventID' => $event['season_event_id'],
            'AttendanceStatus' => 'outside',
            'ServentID' => $admin->PersonID,
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
        ]);

        $this->api($admin)
            ->getJson('/api/attendance/persons?season_event_id='.$event['season_event_id'])
            ->assertOk()
            ->assertJsonPath('persons.0.PersonID', $event['person_id'])
            ->assertJsonPath('persons.0.Status', 'excused');
    }

    public function test_save_marks_booking_and_skips_unbooked_person(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $event = $this->seedReservationEventWithPersonBooking();
        $unbookedPersonId = $this->seedUnbookedPersonInEventSector();

        $this->api($admin)
            ->postJson('/api/attendance/save', [
                'SeasonEventID' => $event['season_event_id'],
                'attendance' => [
                    (string) $event['person_id'] => ['status' => 'present'],
                    (string) $unbookedPersonId => ['status' => 'present'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('saved.0.PersonID', $event['person_id'])
            ->assertJsonPath('skipped.0', $unbookedPersonId);

        $this->assertDatabaseHas('SeasonEventBookingAttendance', [
            'SeasonEventParticipantFinanceID' => $event['booking_id'],
            'SeasonEventID' => $event['season_event_id'],
            'AttendanceStatus' => 'present',
        ]);
        $this->assertSame(0, DB::table('Attendance')->count());
    }

    public function test_excused_save_writes_outside(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $event = $this->seedReservationEventWithPersonBooking();

        $this->api($admin)
            ->postJson('/api/attendance/save', [
                'SeasonEventID' => $event['season_event_id'],
                'attendance' => [
                    (string) $event['person_id'] => ['status' => 'excused'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('saved.0.Status', 'excused');

        $this->assertDatabaseHas('SeasonEventBookingAttendance', [
            'SeasonEventParticipantFinanceID' => $event['booking_id'],
            'AttendanceStatus' => 'outside',
        ]);
    }

    public function test_unmarked_absent_is_not_written(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $event = $this->seedReservationEventWithPersonBooking();

        $this->api($admin)
            ->postJson('/api/attendance/save', [
                'SeasonEventID' => $event['season_event_id'],
                'attendance' => [
                    (string) $event['person_id'] => ['status' => 'absent'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('count', 0);

        $this->assertSame(0, DB::table('SeasonEventBookingAttendance')->count());
        $this->assertSame(0, DB::table('Attendance')->count());
    }

    public function test_super_admin_without_groups_can_list_events_and_save(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $event = $this->seedReservationEventWithPersonBooking();

        $this->api($admin)
            ->getJson('/api/attendance/events?season_id='.$event['season_id'])
            ->assertOk()
            ->assertJsonPath('events.0.SeasonEventID', $event['season_event_id']);

        $this->api($admin)
            ->postJson('/api/attendance/save', [
                'SeasonEventID' => $event['season_event_id'],
                'attendance' => [
                    (string) $event['person_id'] => ['status' => 'present'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('count', 1);
    }

    public function test_khadem_with_sector_overlap_lists_booked_person_from_other_sector(): void
    {
        $khadem = $this->createUserWithRole('Khadem');
        $event = $this->seedReservationEventWithPersonBooking();
        DB::table('PersonGroup')->insert(['PersonID' => $khadem->PersonID, 'GroupID' => 1]);
        DB::table('GroupQetaa')->insert(['GroupID' => 1, 'QetaaID' => 99]);

        $outsider = User::create([
            'FirstName' => 'Other',
            'SecondName' => 'Sector',
            'ThirdName' => 'Scout',
            'ShamandoraCode' => 'X'.uniqid(),
        ]);
        DB::table('PersonQetaa')->insert([
            'PersonID' => $outsider->PersonID,
            'QetaaID' => 7,
        ]);
        DB::table('SeasonEventParticipantFinance')->insert([
            'SeasonEventID' => $event['season_event_id'],
            'PersonID' => $outsider->PersonID,
            'IsRefunded' => 0,
        ]);

        $ids = collect($this->api($khadem)
            ->getJson('/api/attendance/persons?season_event_id='.$event['season_event_id'])
            ->assertOk()
            ->json('persons'))->pluck('PersonID')->all();

        $this->assertEqualsCanonicalizing(
            [$event['person_id'], $outsider->PersonID],
            $ids
        );
    }

    public function test_staff_without_live_or_sector_cannot_save(): void
    {
        $servant = $this->createUserWithRole('Media');
        $event = $this->seedReservationEventWithPersonBooking();

        $this->api($servant)
            ->postJson('/api/attendance/save', [
                'SeasonEventID' => $event['season_event_id'],
                'attendance' => [
                    (string) $event['person_id'] => ['status' => 'present'],
                ],
            ])
            ->assertForbidden();

        $this->assertSame(0, DB::table('SeasonEventBookingAttendance')->count());
    }

    public function test_refunded_booking_is_excluded(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $event = $this->seedReservationEventWithPersonBooking();
        DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventParticipantFinanceID', $event['booking_id'])
            ->update(['IsRefunded' => 1]);

        $this->api($admin)
            ->getJson('/api/attendance/persons?season_event_id='.$event['season_event_id'])
            ->assertOk()
            ->assertJsonPath('persons', []);

        $this->api($admin)
            ->postJson('/api/attendance/save', [
                'SeasonEventID' => $event['season_event_id'],
                'attendance' => [
                    (string) $event['person_id'] => ['status' => 'present'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('skipped.0', $event['person_id']);

        $this->assertSame(0, DB::table('SeasonEventBookingAttendance')->count());
    }

    /**
     * @return array{season_id: int, season_event_id: int, person_id: int, booking_id: int}
     */
    private function seedReservationEventWithPersonBooking(): array
    {
        $seasonId = (int) DB::table('Season')->insertGetId([
            'SeasonName' => 'Test season',
            'SeasonYear' => 2026,
        ]);
        $typeId = (int) DB::table('EventType')->insertGetId([
            'EventTypeName' => 'Camp',
            'TakesReservation' => 1,
        ]);
        $eventId = (int) DB::table('Event')->insertGetId([
            'EventTypeID' => $typeId,
            'EventName' => 'Open camp',
            'EventStartDate' => '2026-08-18',
            'EventEndDate' => '2026-08-20',
        ]);
        $seasonEventId = (int) DB::table('SeasonEvent')->insertGetId([
            'SeasonID' => $seasonId,
            'EventID' => $eventId,
        ]);
        DB::table('EventQetaa')->insert([
            'EventID' => $eventId,
            'QetaaID' => 99,
        ]);

        $booked = User::create([
            'FirstName' => 'Booked',
            'SecondName' => 'Scout',
            'ThirdName' => 'One',
            'ShamandoraCode' => 'B'.uniqid(),
        ]);
        DB::table('PersonQetaa')->insert([
            'PersonID' => $booked->PersonID,
            'QetaaID' => 99,
        ]);
        DB::table('PersonPhoneNumbers')->insert([
            'PersonID' => $booked->PersonID,
            'PersonPersonalMobileNumber' => '01011111111',
        ]);
        $bookingId = (int) DB::table('SeasonEventParticipantFinance')->insertGetId([
            'SeasonEventID' => $seasonEventId,
            'PersonID' => $booked->PersonID,
            'IsRefunded' => 0,
        ]);

        return [
            'season_id' => $seasonId,
            'season_event_id' => $seasonEventId,
            'person_id' => (int) $booked->PersonID,
            'booking_id' => $bookingId,
        ];
    }

    private function seedUnbookedPersonInEventSector(): int
    {
        $person = User::create([
            'FirstName' => 'Unbooked',
            'SecondName' => 'Scout',
            'ThirdName' => 'Two',
            'ShamandoraCode' => 'U'.uniqid(),
        ]);
        DB::table('PersonQetaa')->insert([
            'PersonID' => $person->PersonID,
            'QetaaID' => 99,
        ]);

        return (int) $person->PersonID;
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Kyrillos',
            'SecondName' => $roleName,
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'T'.uniqid(),
        ]);

        $roleId = (int) DB::table('Roles')->insertGetId([
            'RoleName' => $roleName,
        ]);
        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user->fresh();
    }

    private function api(User $user)
    {
        $token = $user->createToken('test-token')->plainTextToken;

        return $this->withHeaders(['Authorization' => "Bearer {$token}"]);
    }

    private function createSchema(): void
    {
        foreach ([
            'SeasonEventBookingAttendance',
            'SeasonEventParticipantFinance',
            'PersonPhoneNumbers',
            'PersonSanaMarhala',
            'SanaMarhala',
            'PersonQetaa',
            'Qetaa',
            'EventQetaa',
            'GroupQetaa',
            'PersonGroup',
            'SeasonEvent',
            'Event',
            'EventType',
            'PersonRole',
            'Roles',
            'PersonInformation',
            'Season',
            'Attendance',
            'personal_access_tokens',
            'Guests',
            'FamilyMembers',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
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
            $table->boolean('TakesReservation')->default(false);
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
            $table->unsignedInteger('SeasonID')->nullable();
            $table->unsignedInteger('EventID');
        });
        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->increments('PersonGroupID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
        });
        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->increments('GroupQetaaID');
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->increments('EventQetaaID');
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName')->nullable();
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('SanaMarhala', function (Blueprint $table) {
            $table->increments('SanaMarhalaID');
            $table->string('SanaMarhalaName')->nullable();
        });
        Schema::create('PersonSanaMarhala', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('SanaMarhalaID');
        });
        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->increments('PersonPhoneNumberID');
            $table->unsignedInteger('PersonID');
            $table->string('PersonPersonalMobileNumber')->nullable();
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
            $table->unsignedTinyInteger('IsRefunded')->default(0);
        });
        Schema::create('SeasonEventBookingAttendance', function (Blueprint $table) {
            $table->increments('SeasonEventBookingAttendanceID');
            $table->unsignedInteger('SeasonEventParticipantFinanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->string('AttendanceStatus', 20);
            $table->unsignedInteger('ServentID');
            $table->timestamp('CreatedAt')->nullable();
            $table->timestamp('UpdatedAt')->nullable();
        });
        Schema::create('Attendance', function (Blueprint $table) {
            $table->increments('AttendanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedInteger('ServedID');
            $table->unsignedInteger('ServentID');
            $table->string('AttendanceStatus');
            $table->text('Excuse')->nullable();
            $table->unique(['SeasonEventID', 'ServedID']);
        });
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuidMorphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
}
