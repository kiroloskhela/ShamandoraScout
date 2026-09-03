<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_permissions_policy_allows_same_origin_camera(): void
    {
        $policy = $this->get('/login-auth')
            ->assertOk()
            ->headers
            ->get('Permissions-Policy');

        $this->assertSame('geolocation=(), microphone=(), camera=(self)', $policy);
    }
}
