<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_returns_ok_when_database_is_up(): void
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
            ]);
    }
}
