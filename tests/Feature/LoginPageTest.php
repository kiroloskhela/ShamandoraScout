<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page_returns_successful_response(): void
    {
        $response = $this->get('/login-auth');
        $response->assertStatus(200);
    }
}
