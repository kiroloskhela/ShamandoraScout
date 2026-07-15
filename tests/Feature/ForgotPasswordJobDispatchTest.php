<?php

namespace Tests\Feature;

use App\Jobs\SendPasswordResetLinkMail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ForgotPasswordJobDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('ForgotPasswordJobDispatchTest requires sqlite in-memory.');
        }

        config([
            'services.whatsapp.bridge_url' => 'http://127.0.0.1:3010/send',
            'services.whatsapp.bridge_token' => 'test-token',
        ]);

        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('PersonPhoneNumbers');
        Schema::dropIfExists('PersonInformation');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
            $table->string('PersonalEmail')->nullable();
            $table->date('DateOfBirth')->nullable();
            $table->string('RaqamQawmy')->nullable();
        });

        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->increments('PersonPhoneNumbersID');
            $table->unsignedInteger('PersonID');
            $table->string('PersonPersonalMobileNumber')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_forgot_password_sends_whatsapp_link_and_dispatches_mail_job(): void
    {
        Bus::fake();
        Http::fake([
            'http://127.0.0.1:3010/send' => Http::response(['ok' => true, 'to' => '201000485402', 'messageId' => '1'], 200),
        ]);

        $personId = DB::table('PersonInformation')->insertGetId([
            'FirstName' => 'Kiro',
            'SecondName' => 'Test',
            'ThirdName' => null,
            'FourthName' => null,
            'PersonalEmail' => 'kiro@example.com',
            'DateOfBirth' => '2001-03-29',
        ]);

        DB::table('PersonPhoneNumbers')->insert([
            'PersonID' => $personId,
            'PersonPersonalMobileNumber' => '01000485402',
        ]);

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'phone' => '01000485402',
            'dob' => '2001-03-29',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('success');

        Http::assertSent(function (HttpRequest $request) {
            return $request->url() === 'http://127.0.0.1:3010/send'
                && $request->hasHeader('X-Bridge-Token', 'test-token')
                && str_contains((string) $request['message'], '/reset-password/')
                && ($request['full_number'] === '+201000485402' || $request['full_number'] === '+201000485402');
        });

        Bus::assertDispatched(SendPasswordResetLinkMail::class, function (SendPasswordResetLinkMail $job) use ($personId) {
            return $job->toEmail === 'kiro@example.com'
                && $job->personId === (string) $personId
                && str_contains($job->resetUrl, '/reset-password/');
        });
    }

    public function test_forgot_password_whatsapp_works_without_email(): void
    {
        Bus::fake();
        Http::fake([
            'http://127.0.0.1:3010/send' => Http::response(['ok' => true, 'to' => '201000485402', 'messageId' => '1'], 200),
        ]);

        $personId = DB::table('PersonInformation')->insertGetId([
            'FirstName' => 'No',
            'SecondName' => 'Email',
            'PersonalEmail' => null,
            'DateOfBirth' => '2000-01-01',
        ]);

        DB::table('PersonPhoneNumbers')->insert([
            'PersonID' => $personId,
            'PersonPersonalMobileNumber' => '01012345678',
        ]);

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'phone' => '01012345678',
            'dob' => '2000-01-01',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas('success');
        Bus::assertNotDispatched(SendPasswordResetLinkMail::class);
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'person-' . $personId . '@password-reset.local',
        ]);
    }
}
