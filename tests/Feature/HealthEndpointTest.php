<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_public_health_endpoint_is_minimal_when_database_is_up(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('status', 'healthy')
            ->assertJsonStructure([
                'ok',
                'status',
                'checks' => ['app', 'database'],
                'time',
            ])
            ->assertJsonMissingPath('release')
            ->assertJsonMissingPath('log_channel')
            ->assertJsonMissingPath('checks.failed_jobs')
            ->assertJsonMissingPath('checks.redis');
    }

    public function test_health_details_require_configured_token(): void
    {
        config(['app.health_token' => 'secret-ops-token']);

        $this->getJson('/health?token=wrong')
            ->assertOk()
            ->assertJsonMissingPath('release')
            ->assertJsonMissingPath('checks.failed_jobs');

        $this->getJson('/health?token=secret-ops-token')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure([
                'ok',
                'status',
                'checks' => ['app', 'database', 'failed_jobs'],
                'release',
                'log_channel',
                'time',
            ]);
    }

    public function test_health_details_accept_header_token(): void
    {
        config(['app.health_token' => 'secret-ops-token']);

        $this->withHeaders(['X-Health-Token' => 'secret-ops-token'])
            ->getJson('/health')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['release', 'log_channel']);
    }
}
