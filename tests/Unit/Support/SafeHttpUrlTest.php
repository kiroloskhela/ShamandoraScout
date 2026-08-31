<?php

namespace Tests\Unit\Support;

use App\Support\SafeHttpUrl;
use Tests\TestCase;

class SafeHttpUrlTest extends TestCase
{
    public function test_accepts_http_and_https(): void
    {
        $this->assertSame('https://example.com/a', SafeHttpUrl::sanitize('https://example.com/a'));
        $this->assertSame('http://example.com', SafeHttpUrl::sanitize('http://example.com'));
        $this->assertTrue(SafeHttpUrl::isSafe('https://example.com'));
    }

    public function test_rejects_non_http_schemes_and_empty(): void
    {
        $this->assertNull(SafeHttpUrl::sanitize('javascript:alert(1)'));
        $this->assertNull(SafeHttpUrl::sanitize('data:text/html,hi'));
        $this->assertNull(SafeHttpUrl::sanitize('ftp://files.example/a'));
        $this->assertNull(SafeHttpUrl::sanitize(''));
        $this->assertNull(SafeHttpUrl::sanitize(null));
        $this->assertFalse(SafeHttpUrl::isSafe('not a url'));
    }
}
