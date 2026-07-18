<?php

namespace Tests\Feature;

use App\Support\AppSettings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VersionApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('AppSettings');
        Schema::create('AppSettings', function (Blueprint $table) {
            $table->string('SettingKey', 100)->primary();
            $table->text('SettingValue')->nullable();
            $table->timestamp('UpdatedAt')->nullable();
            $table->unsignedBigInteger('UpdatedByPersonID')->nullable();
        });

        Cache::flush();
    }

    public function test_version_check_reads_app_settings_overrides(): void
    {
        AppSettings::set('android_latest_version', '2.0.0');
        AppSettings::set('android_min_version', '1.5.0');
        AppSettings::set('android_force_update', '0');
        AppSettings::set('android_url', 'https://example.com/android');

        $response = $this->getJson('/api/version/check?platform=android&version=1.0.0');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.android.latest_version', '2.0.0')
            ->assertJsonPath('data.android.min_version', '1.5.0')
            ->assertJsonPath('data.needs_update', true)
            ->assertJsonPath('data.force_update', true);
    }

    public function test_version_check_falls_back_to_config_when_unset(): void
    {
        $response = $this->getJson('/api/version/check?platform=ios&version=9.9.9');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.needs_update', false);
    }
}
