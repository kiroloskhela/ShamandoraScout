<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Attendance save only upserts PersonIDs inside the servant's allowed Qetaas.
 */
class AttendanceApiAllowListTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'Attendance',
            'PersonQetaa',
            'EventQetaa',
            'GroupQetaa',
            'PersonGroup',
            'SeasonEvent',
            'PersonInformation',
            'personal_access_tokens',
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

        Schema::create('SeasonEvent', function (Blueprint $table) {
            $table->increments('SeasonEventID');
            $table->unsignedInteger('EventID');
        });

        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
        });

        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });

        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
        });

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
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

    public function test_save_skips_unauthorized_person_ids_and_reports_them(): void
    {
        $servant = User::create([
            'FirstName' => 'Servant',
            'SecondName' => 'One',
            'ThirdName' => 'A',
            'ShamandoraCode' => 'SV'.uniqid(),
        ]);
        $allowedPersonId = 100;
        $blockedPersonId = 999;

        DB::table('SeasonEvent')->insert(['SeasonEventID' => 1, 'EventID' => 7]);
        DB::table('PersonGroup')->insert(['PersonID' => $servant->PersonID, 'GroupID' => 5]);
        DB::table('GroupQetaa')->insert(['GroupID' => 5, 'QetaaID' => 3]);
        DB::table('EventQetaa')->insert(['EventID' => 7, 'QetaaID' => 3]);
        DB::table('PersonQetaa')->insert(['PersonID' => $allowedPersonId, 'QetaaID' => 3]);

        $token = $servant->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/attendance/save', [
                'SeasonEventID' => 1,
                'attendance' => [
                    (string) $allowedPersonId => ['status' => 'present'],
                    (string) $blockedPersonId => ['status' => 'absent'],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('saved.0.PersonID', $allowedPersonId)
            ->assertJsonPath('skipped.0', $blockedPersonId);

        $this->assertDatabaseHas('Attendance', [
            'SeasonEventID' => 1,
            'ServedID' => $allowedPersonId,
            'AttendanceStatus' => 'present',
        ]);
        $this->assertDatabaseMissing('Attendance', [
            'SeasonEventID' => 1,
            'ServedID' => $blockedPersonId,
        ]);
    }
}
