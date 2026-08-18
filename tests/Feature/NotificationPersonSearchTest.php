<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificationPersonSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->createSchema();
    }

    public function test_page_uses_search_instead_of_person_dropdown(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $other = User::create([
            'FirstName' => 'Zzunique',
            'SecondName' => 'Scout',
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'P'.uniqid(),
        ]);

        $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('الاسم أو رقم الهوية أو رقم الموبايل', false)
            ->assertSee('id="person_search"', false)
            ->assertDontSee('-- اختر شخص --', false)
            ->assertDontSee('Zzunique Scout', false);
    }

    public function test_khadem_cannot_open_notifications(): void
    {
        $khadem = $this->createUserWithRole('Khadem');

        $this->actingAs($khadem)
            ->get(route('notifications.index'))
            ->assertForbidden();
    }

    public function test_send_requires_an_existing_person(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');

        $this->actingAs($admin)
            ->from(route('notifications.index'))
            ->post(route('notifications.send'), [
                'title' => 'Hello',
                'body' => 'World',
            ])
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHasErrors('person_id');
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Notify',
            'SecondName' => $roleName,
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'N'.uniqid(),
        ]);
        $roleId = (int) DB::table('Roles')->insertGetId(['RoleName' => $roleName]);
        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user->fresh();
    }

    private function createSchema(): void
    {
        foreach ([
            'PersonRole',
            'Roles',
            'PersonImages',
            'PersonInformation',
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
    }
}
