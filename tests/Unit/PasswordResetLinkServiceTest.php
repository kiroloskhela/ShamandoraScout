<?php

namespace Tests\Unit;

use App\Domain\Auth\PasswordResetLinkService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PasswordResetLinkServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('password_reset_tokens');
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_issue_reset_url_stores_hashed_token_and_validates(): void
    {
        $service = new PasswordResetLinkService(60);
        $url = $service->issueResetUrl('person@example.com');

        $this->assertStringContainsString('/reset-password/', $url);
        $this->assertSame(
            'person-7@password-reset.local',
            $service->tokenKeyForPerson(7, null)
        );
        $this->assertSame(
            'a@b.com',
            $service->tokenKeyForPerson(7, 'A@B.com')
        );
        $this->assertStringContainsString('email=person%40example.com', $url);

        $row = DB::table('password_reset_tokens')->where('email', 'person@example.com')->first();
        $this->assertNotNull($row);

        $path = parse_url($url, PHP_URL_PATH);
        $token = urldecode(basename($path));

        $this->assertNotSame($token, $row->token);
        $this->assertTrue(Hash::check($token, $row->token));
        $this->assertTrue($service->tokenIsValid('person@example.com', $token));
        $this->assertFalse($service->tokenIsValid('person@example.com', 'wrong-token'));
    }

    public function test_expired_token_is_invalid(): void
    {
        $service = new PasswordResetLinkService(60);
        $url = $service->issueResetUrl('person@example.com');
        $path = parse_url($url, PHP_URL_PATH);
        $token = urldecode(basename($path));

        DB::table('password_reset_tokens')->where('email', 'person@example.com')->update([
            'created_at' => Carbon::now()->subMinutes(61),
        ]);

        $this->assertFalse($service->tokenIsValid('person@example.com', $token));
    }

    public function test_consume_token_removes_row(): void
    {
        $service = new PasswordResetLinkService(60);
        $service->issueResetUrl('person@example.com');
        $service->consumeToken('person@example.com');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'person@example.com']);
    }
}
