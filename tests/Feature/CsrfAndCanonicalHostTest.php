<?php

namespace Tests\Feature;

use Tests\TestCase;

class CsrfAndCanonicalHostTest extends TestCase
{
    public function test_csrf_token_endpoint_returns_stable_token_and_is_not_cached(): void
    {
        $first = $this->getJson(route('csrf.token'));
        $first->assertOk()
            ->assertJsonStructure(['token'])
            ->assertHeader('Cache-Control', 'no-store, private');

        $token = $first->json('token');
        $this->assertNotEmpty($token);

        $this->getJson(route('csrf.token'))
            ->assertOk()
            ->assertJsonPath('token', $token);
    }

    public function test_canonical_host_does_not_redirect_outside_production(): void
    {
        config(['app.url' => 'https://shamandorascout.com']);

        $this->get('https://www.shamandorascout.com/csrf-token')
            ->assertOk();
    }

    public function test_canonical_host_redirects_get_in_production_using_app_url_only(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://shamandorascout.com']);

        $this->get('https://www.shamandorascout.com/login-auth?next=1')
            ->assertRedirect('https://shamandorascout.com/login-auth?next=1');
    }

    public function test_canonical_host_does_not_redirect_post(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://shamandorascout.com']);

        $this->post('https://www.shamandorascout.com/login', [
            'person_id' => '1',
            'person_password' => 'secret',
        ])->assertStatus(419);
    }

    public function test_canonical_host_skips_health_and_loopback(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://shamandorascout.com']);

        $this->get('https://www.shamandorascout.com/health')
            ->assertOk();

        $this->get('http://127.0.0.1/login-auth')
            ->assertOk();
    }

    public function test_html_post_without_csrf_shows_page_expired(): void
    {
        $this->app['env'] = 'production';

        $this->post('/liveform/step2', [
            'first_name' => 'تجربة',
        ])->assertStatus(419)
            ->assertSee(__('Page expired'), false);
    }
}
