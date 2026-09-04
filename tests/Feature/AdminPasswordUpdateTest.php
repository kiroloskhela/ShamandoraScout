<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminPasswordController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdminPasswordUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['PersonPhoneNumbers', 'PersonSystemPassword', 'PersonInformation', 'refresh_tokens', 'personal_access_tokens'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
            $table->string('RaqamQawmy')->nullable();
            $table->string('PersonalEmail')->nullable();
        });
        Schema::create('PersonSystemPassword', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('Password');
        });
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('token_hash', 64)->unique();
            $table->uuid('family_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('replaced_by_id')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
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
        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->increments('PersonPhoneNumberID');
            $table->unsignedInteger('PersonID');
            $table->string('PersonPersonalMobileNumber')->nullable();
        });
    }

    public static function weakPasswords(): array
    {
        return [
            'too_short' => ['Ab1'],
            'no_upper' => ['password1'],
            'no_lower' => ['PASSWORD1'],
            'no_number' => ['Password'],
        ];
    }

    #[DataProvider('weakPasswords')]
    public function test_weak_password_is_rejected(string $password): void
    {
        $user = $this->person();
        $request = Request::create('/admin/passwords/'.$user->PersonID.'/update', 'POST', [
            'password' => $password,
        ]);

        $this->expectException(ValidationException::class);
        app(AdminPasswordController::class)->update($request, $user->PersonID);
    }

    public function test_strong_password_is_saved_and_flashes_success(): void
    {
        $user = $this->person();
        $request = Request::create('/admin/passwords/'.$user->PersonID.'/update', 'POST', [
            'password' => 'Passw0rd',
        ]);

        $response = app(AdminPasswordController::class)->update($request, $user->PersonID);

        $this->assertTrue(Hash::check('Passw0rd', (string) DB::table('PersonSystemPassword')->where('PersonID', $user->PersonID)->value('Password')));
        $this->assertTrue($response->isRedirect(route('admin.passwords.edit', $user->PersonID)));
        $this->assertSame(__('Password updated successfully.'), $response->getSession()->get('success'));
        $this->assertNull($response->getSession()->get('password'));
        $this->assertNull($response->getSession()->getOldInput('password'));
    }

    public function test_missing_person_is_not_found(): void
    {
        try {
            app(AdminPasswordController::class)->edit(999999);
            $this->fail('Expected 404');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }

        $request = Request::create('/admin/passwords/999999/update', 'POST', [
            'password' => 'Passw0rd',
        ]);
        try {
            app(AdminPasswordController::class)->update($request, 999999);
            $this->fail('Expected 404');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }

        $this->assertDatabaseMissing('PersonSystemPassword', ['PersonID' => 999999]);
    }

    private function person(): User
    {
        return User::create([
            'FirstName' => 'Ada',
            'SecondName' => 'Scout',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'SH-1',
        ]);
    }
}
