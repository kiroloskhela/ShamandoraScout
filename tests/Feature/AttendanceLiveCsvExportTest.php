<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttendanceLiveCsvExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->createSchema();
    }

    public function test_super_admin_downloads_live_attendance_csv(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        [, $seasonEventId, $personId, $guestId] = $this->seedReservationEvent();

        $response = $this->actingAs($admin)
            ->get(route('attendance.live.csv', ['season_event_id' => $seasonEventId]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'attendance-live-'.$seasonEventId.'.csv',
            (string) $response->headers->get('content-disposition')
        );

        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $lines = $this->csvLines($csv);

        $this->assertSame([
            __('ID'),
            __('Name'),
            __('Phone'),
            __('Sector'),
            __('Status'),
            __('Updated'),
        ], $lines[0]);

        $byId = collect($lines)->skip(1)->keyBy(0);
        $this->assertCount(3, $lines);

        $person = $byId[(string) $personId];
        $this->assertSame("'=CMD", $person[1]);
        $this->assertSame('01011111111', $person[2]);
        $this->assertSame('جوالة', $person[3]);
        $this->assertSame(__('Present'), $person[4]);
        $this->assertSame('2026-09-03 08:00:00', $person[5]);

        $guest = $byId[(string) $guestId];
        $this->assertSame('Guest Booker', $guest[1]);
        $this->assertSame('01000000000', $guest[2]);
        $this->assertSame(__('Guests'), $guest[3]);
        $this->assertSame(__('Not scanned'), $guest[4]);
        $this->assertSame('', $guest[5]);
    }

    public function test_csv_export_forbidden_without_live_role(): void
    {
        $servant = $this->createUserWithRole('Media');
        [, $seasonEventId] = $this->seedReservationEvent();

        $this->actingAs($servant)
            ->get(route('attendance.live.csv', ['season_event_id' => $seasonEventId]))
            ->assertForbidden();
    }

    public function test_csv_export_rejects_non_reservation_event(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $typeId = (int) DB::table('EventType')->insertGetId([
            'EventTypeName' => 'Meeting',
            'TakesReservation' => 0,
        ]);
        $eventId = (int) DB::table('Event')->insertGetId([
            'EventTypeID' => $typeId,
            'EventName' => 'Closed meeting',
            'EventStartDate' => '2026-09-01',
            'EventEndDate' => '2026-09-01',
        ]);
        $seasonId = (int) DB::table('Season')->insertGetId([
            'SeasonName' => 'Test season',
            'SeasonYear' => 2026,
        ]);
        $seasonEventId = (int) DB::table('SeasonEvent')->insertGetId([
            'SeasonID' => $seasonId,
            'EventID' => $eventId,
        ]);

        $this->actingAs($admin)
            ->get(route('attendance.live.csv', ['season_event_id' => $seasonEventId]))
            ->assertStatus(422);
    }

    /**
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function seedReservationEvent(): array
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
        DB::table('SeasonEventFinance')->insert([
            'SeasonEventID' => $seasonEventId,
            'SendQrWhatsApp' => 0,
        ]);

        $qetaaId = (int) DB::table('Qetaa')->insertGetId([
            'QetaaName' => 'جوالة',
        ]);

        $personId = (int) DB::table('PersonInformation')->insertGetId([
            'FirstName' => '=CMD',
            'SecondName' => '',
            'ThirdName' => '',
            'FourthName' => '',
        ]);
        DB::table('PersonPhoneNumbers')->insert([
            'PersonID' => $personId,
            'PersonPersonalMobileNumber' => '01011111111',
        ]);
        DB::table('PersonQetaa')->insert([
            'PersonID' => $personId,
            'QetaaID' => $qetaaId,
        ]);
        $personBookingId = (int) DB::table('SeasonEventParticipantFinance')->insertGetId([
            'SeasonEventID' => $seasonEventId,
            'PersonID' => $personId,
            'IsRefunded' => 0,
        ]);
        DB::table('SeasonEventBookingAttendance')->insert([
            'SeasonEventParticipantFinanceID' => $personBookingId,
            'SeasonEventID' => $seasonEventId,
            'AttendanceStatus' => 'present',
            'ServentID' => 1,
            'CreatedAt' => '2026-09-03 08:00:00',
            'UpdatedAt' => '2026-09-03 08:00:00',
        ]);

        $guestId = (int) DB::table('Guests')->insertGetId([
            'FirstName' => 'Guest',
            'SecondName' => 'Booker',
            'ThirdName' => '',
            'FourthName' => '',
            'MobileNumber' => '01000000000',
        ]);
        DB::table('SeasonEventParticipantFinance')->insert([
            'SeasonEventID' => $seasonEventId,
            'GuestID' => $guestId,
            'IsRefunded' => 0,
        ]);

        $refundedId = (int) DB::table('PersonInformation')->insertGetId([
            'FirstName' => 'Refunded',
            'SecondName' => 'Person',
            'ThirdName' => '',
            'FourthName' => '',
        ]);
        DB::table('SeasonEventParticipantFinance')->insert([
            'SeasonEventID' => $seasonEventId,
            'PersonID' => $refundedId,
            'IsRefunded' => 1,
        ]);

        return [$seasonId, $seasonEventId, $personId, $guestId];
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

    /**
     * @return array<int, array<int, string>>
     */
    private function csvLines(string $csv): array
    {
        $csv = substr($csv, 3);
        $lines = preg_split("/\r\n|\n|\r/", trim($csv)) ?: [];

        return array_map(str_getcsv(...), $lines);
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
            'PersonQetaa',
            'Qetaa',
            'EventQetaa',
            'SeasonEvent',
            'Event',
            'EventType',
            'PersonRole',
            'Roles',
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
