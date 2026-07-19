<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page_returns_successful_response(): void
    {
        $response = $this->get('/login-auth');
        $response->assertStatus(200);
        $response->assertSee('shamandora.webp', false);
        $response->assertSee('id="capsLockWarning"', false);
    }

    public function test_login_validation_requires_person_id_and_password(): void
    {
        $response = $this->from('/login-auth')->post('/login', []);

        $response->assertRedirect('/login-auth');
        $response->assertSessionHasErrors(['person_id', 'person_password']);
    }
}
