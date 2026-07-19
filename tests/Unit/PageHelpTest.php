<?php

namespace Tests\Unit;

use App\Support\PageHelp;
use Tests\TestCase;

class PageHelpTest extends TestCase
{
    public function test_resolves_exact_route_help_key(): void
    {
        $this->assertSame('finance.index', PageHelp::resolveKey('finance.index'));
        $this->assertSame('person.index', PageHelp::resolveKey('person.index'));
        $this->assertSame('person.new-enrolments-index', PageHelp::resolveKey('person.new-enrolments-index'));
        $this->assertSame('finance.edit', PageHelp::resolveKey('finance.edit'));
    }

    public function test_falls_back_for_family_routes(): void
    {
        $this->assertSame('person.new-enrolments-index', PageHelp::resolveKey('person.new-enrolments-approve'));
        $this->assertSame('games.index', PageHelp::resolveKey('games.destroy'));
    }

    public function test_unknown_route_uses_default_content(): void
    {
        $this->assertSame('default', PageHelp::resolveKey('totally.unknown.route'));

        $content = PageHelp::content('totally.unknown.route');
        $this->assertSame('default', $content['key']);
        $this->assertNotSame('', $content['title']);
        $this->assertNotEmpty($content['steps']);
    }

    public function test_arabic_locale_returns_translated_help(): void
    {
        app()->setLocale('ar');

        $content = PageHelp::content('person.index');
        $this->assertNotSame('', $content['title']);
        $this->assertNotEmpty($content['steps']);
        // Must be Arabic text, not the English fallback key/title from en file.
        $this->assertNotSame('Members data', $content['title']);
        $this->assertMatchesRegularExpression('/\p{Arabic}/u', $content['title']);
    }

    public function test_admin_passwords_and_edit_both_resolve(): void
    {
        $this->assertSame('admin.passwords', PageHelp::resolveKey('admin.passwords'));
        $this->assertSame('admin.passwords.edit', PageHelp::resolveKey('admin.passwords.edit'));

        $index = PageHelp::content('admin.passwords');
        $edit = PageHelp::content('admin.passwords.edit');
        $this->assertNotSame($index['title'], $edit['title']);
    }
}
