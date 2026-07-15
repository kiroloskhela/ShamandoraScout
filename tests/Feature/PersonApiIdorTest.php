<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Person profile/calendar: route {id} is honored only when PersonPolicy allows
 * (self or SuperAdmin/AdminQetaa). Others get 403 — not silently remapped to self.
 */
class PersonApiIdorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('PersonInformation');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('ShamandoraCode')->nullable();
        });

        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('RoleID');
            $table->string('RoleName');
            $table->text('RoleDescription')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
            $table->unsignedInteger('RequestPersonID')->nullable();
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

    public function test_show_persons_route_requires_authentication(): void
    {
        $this->getJson('/api/show-persons?id=999')->assertUnauthorized();
    }

    public function test_show_profile_route_requires_authentication(): void
    {
        $this->getJson('/api/person/999')->assertUnauthorized();
    }

    public function test_show_calendar_route_requires_authentication(): void
    {
        $this->getJson('/api/calendar/999')->assertUnauthorized();
    }

    public function test_non_elevated_user_cannot_view_another_profile(): void
    {
        $victim = User::create([
            'FirstName' => 'Victim',
            'SecondName' => 'X',
            'ThirdName' => 'Y',
            'ShamandoraCode' => 'V1',
        ]);
        $attacker = User::create([
            'FirstName' => 'Attacker',
            'SecondName' => 'X',
            'ThirdName' => 'Y',
            'ShamandoraCode' => 'A1',
        ]);

        $token = $attacker->createToken('test-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($headers)->getJson('/api/person/'.$victim->PersonID)->assertForbidden();
        $this->withHeaders($headers)->getJson('/api/calendar/'.$victim->PersonID)->assertForbidden();
    }
}
