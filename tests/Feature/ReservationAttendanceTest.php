<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReservationAttendanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->createSchema();
    }

    public function test_super_admin_sees_booked_guest_on_reservation_event_without_sector_overlap(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        [$seasonId, $seasonEventId, $bookingId] = $this->seedReservationEventWithGuest();

        $this->actingAs($admin)
            ->get(route('attendance.manage', [
                'season_id' => $seasonId,
                'season_event_id' => $seasonEventId,
            ]))
            ->assertOk()
            ->assertSee('Guest Booker', false)
            ->assertSee('name="attendance['.$bookingId.'][status]"', false);
    }

    public function test_super_admin_marks_booking_present_and_ignores_foreign_booking_id(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        [$seasonId, $seasonEventId, $bookingId] = $this->seedReservationEventWithGuest();
        $foreignBookingId = $this->seedForeignBooking();

        $this->actingAs($admin)
            ->post(route('attendance.save', $seasonEventId), [
                'season_id' => $seasonId,
                'attendance' => [
                    $bookingId => ['status' => 'present'],
                    $foreignBookingId => ['status' => 'present'],
                ],
            ])
            ->assertRedirect(route('attendance.manage', [
                'season_id' => $seasonId,
                'season_event_id' => $seasonEventId,
            ]));

        $this->assertDatabaseHas('SeasonEventBookingAttendance', [
            'SeasonEventParticipantFinanceID' => $bookingId,
            'SeasonEventID' => $seasonEventId,
            'AttendanceStatus' => 'present',
        ]);
        $this->assertDatabaseMissing('SeasonEventBookingAttendance', [
            'SeasonEventParticipantFinanceID' => $foreignBookingId,
        ]);
    }

    public function test_unmarked_booking_is_not_written(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        [$seasonId, $seasonEventId, $bookingId] = $this->seedReservationEventWithGuest();

        $this->actingAs($admin)
            ->post(route('attendance.save', $seasonEventId), [
                'season_id' => $seasonId,
                'attendance' => [
                    $bookingId => ['status' => null],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(0, DB::table('SeasonEventBookingAttendance')->count());
    }

    public function test_servant_without_live_or_sector_cannot_save_reservation_attendance(): void
    {
        $servant = $this->createUserWithRole('Media');
        [, $seasonEventId, $bookingId] = $this->seedReservationEventWithGuest();

        $this->actingAs($servant)
            ->post(route('attendance.save', $seasonEventId), [
                'attendance' => [
                    $bookingId => ['status' => 'present'],
                ],
            ])
            ->assertForbidden();

        $this->assertSame(0, DB::table('SeasonEventBookingAttendance')->count());
    }

    private function seedReservationEventWithGuest(): array
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
        DB::table('SeasonEventFinance')->insert([
            'SeasonEventID' => $seasonEventId,
            'SendQrWhatsApp' => 0,
        ]);
        $guestId = (int) DB::table('Guests')->insertGetId([
            'FirstName' => 'Guest',
            'SecondName' => 'Booker',
            'ThirdName' => '',
            'FourthName' => '',
            'MobileNumber' => '01000000000',
        ]);
        $bookingId = (int) DB::table('SeasonEventParticipantFinance')->insertGetId([
            'SeasonEventID' => $seasonEventId,
            'GuestID' => $guestId,
            'IsRefunded' => 0,
        ]);

        return [$seasonId, $seasonEventId, $bookingId];
    }

    private function seedForeignBooking(): int
    {
        return (int) DB::table('SeasonEventParticipantFinance')->insertGetId([
            'SeasonEventID' => 9999,
            'GuestID' => 1,
            'IsRefunded' => 0,
        ]);
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

    private function createSchema(): void
    {
        foreach ([
            'SeasonEventBookingAttendance',
            'SeasonEventParticipantFinance',
            'SeasonEventFinance',
            'Guests',
            'FamilyMembers',
            'PersonPhoneNumbers',
            'EventQetaa',
            'GroupQetaa',
            'PersonGroup',
            'SeasonEvent',
            'Event',
            'EventType',
            'PersonRole',
            'Roles',
            'PersonImages',
            'PersonInformation',
            'Season',
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
            $table->unsignedInteger('SeasonID');
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
        Schema::create('SeasonEventFinance', function (Blueprint $table) {
            $table->increments('SeasonEventFinanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedTinyInteger('SendQrWhatsApp')->default(0);
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
        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->increments('PersonPhoneNumberID');
            $table->unsignedInteger('PersonID');
            $table->string('PersonPersonalMobileNumber')->nullable();
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
    }
}
