<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoAeoEntityTest extends TestCase
{
    public function test_public_landing_is_available_to_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Shamandora Scout', false);
        $response->assertSee('الشمندوره البحريه', false);
        $response->assertSee('https://www.facebook.com/ShamandoraScout', false);
        $response->assertSee('https://www.instagram.com/shamandora_scout', false);
        $response->assertSee('"@type": [', false);
        $response->assertSee('SportsOrganization', false);
        $response->assertSee('WebSite', false);
        $response->assertSee('rel="noopener noreferrer me"', false);
        $response->assertDontSee('noindex, nofollow', false);
    }

    public function test_auth_dashboard_requires_login(): void
    {
        $this->get('/home')->assertRedirect(route('login-auth'));
    }

    public function test_sitemap_lists_only_allowlisted_public_urls(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee(rtrim((string) config('app.url'), '/').'/', false);
        $response->assertSee('/feedback', false);
        $response->assertDontSee('/liveform', false);
        $response->assertDontSee('/home', false);
        $response->assertDontSee('/login-auth', false);
    }

    public function test_robots_txt_points_to_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Sitemap:', false);
        $response->assertSee('sitemap.xml', false);
    }

    public function test_login_page_links_official_social_profiles(): void
    {
        $response = $this->get(route('login-auth'));

        $response->assertOk();
        $response->assertSee('https://www.facebook.com/ShamandoraScout', false);
        $response->assertSee('https://www.instagram.com/shamandora_scout', false);
        $response->assertSee(route('landing'), false);
    }
}
