<?php

namespace Tests\Feature;

use App\Jobs\SendPasswordResetLinkMail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ForgotPasswordJobDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_forgot_password_dispatches_send_password_reset_link_mail_job(): void
    {
        Bus::fake();

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

        Bus::assertDispatched(SendPasswordResetLinkMail::class, function (SendPasswordResetLinkMail $job) use ($personId) {
            return $job->toEmail === 'kiro@example.com'
                && $job->personId === (string) $personId
                && str_contains($job->resetUrl, '/reset-password/');
        });
    }
}
