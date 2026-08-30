<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LocaleAndThemeShellTest extends TestCase
{
    public function test_locale_switch_ignores_off_site_referer(): void
    {
        $this->withoutVite();

        $this->from('https://evil.example/phish')
            ->get(route('locale.switch', 'en'))
            ->assertRedirect(url('/'));

        $this->assertSame('en', session('locale'));
    }

    public function test_can_switch_locale_to_english(): void
    {
        $this->withoutVite();

        $this->from('/login-auth')
            ->get(route('locale.switch', 'en'))
            ->assertRedirect();

        $this->assertSame('en', session('locale'));
    }

    public function test_login_page_renders_english_copy(): void
    {
        $this->withoutVite();

        $this->withSession(['locale' => 'en'])
            ->get(route('login-auth'))
            ->assertOk()
            ->assertSee('Log in', false)
            ->assertSee('Person ID', false);
    }

    public function test_login_page_renders_arabic_copy_by_default(): void
    {
        $this->withoutVite();

        $this->withSession(['locale' => 'ar'])
            ->get(route('login-auth'))
            ->assertOk()
            ->assertSee('تسجيل الدخول', false)
            ->assertSee('رقم الهوية', false);
    }

    public function test_forgot_password_page_renders_english_copy(): void
    {
        $this->withoutVite();

        $this->withSession(['locale' => 'en'])
            ->get(route('forgot-password.form'))
            ->assertOk()
            ->assertSee('Reset password', false);
    }

    public function test_english_dashboard_renders_translated_copy(): void
    {
        $this->withoutVite();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('sqlite required for dashboard locale test.');
        }

        $this->createMinimalDashboardSchema();

        $user = User::create([
            'FirstName' => 'Test',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'LOC' . uniqid(),
        ]);
        $this->grantStaffRole($user);

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get(route('home'));

        $response->assertOk();
        $html = $response->getContent();
        $this->assertTrue(
            str_contains($html, 'Dashboard') || str_contains($html, 'Current members count'),
            'Expected English dashboard copy (Dashboard or Current members count).'
        );
    }

    private function createMinimalDashboardSchema(): void
    {
        foreach ([
            'Season',
            'SeasonEvent',
            'EventType',
            'Event',
            'EventQetaa',
            'PersonGroup',
            'GroupQetaa',
            'Qetaa',
            'PersonQetaa',
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
