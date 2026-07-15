<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleAndThemeShellTest extends TestCase
{
    public function test_can_switch_locale_to_english(): void
    {
        $this->withoutVite();

        $this->from('/login-auth')
            ->get(route('locale.switch', 'en'))
            ->assertRedirect();

        $this->assertSame('en', session('locale'));
    }

    public function test_login_page_renders_english_copy(): void
    {
        $this->withoutVite();

        $this->withSession(['locale' => 'en'])
            ->get(route('login-auth'))
            ->assertOk()
            ->assertSee('Log in', false)
            ->assertSee('Person ID', false);
    }

    public function test_login_page_renders_arabic_copy_by_default(): void
    {
        $this->withoutVite();

        $this->withSession(['locale' => 'ar'])
            ->get(route('login-auth'))
            ->assertOk()
            ->assertSee('تسجيل الدخول', false)
            ->assertSee('رقم الهوية', false);
    }
}
