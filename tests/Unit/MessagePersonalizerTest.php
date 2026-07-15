<?php

namespace Tests\Unit;

use App\Domain\WhatsApp\MessagePersonalizer;
use Tests\TestCase;

class MessagePersonalizerTest extends TestCase
{
    public function test_replaces_name_from_parts(): void
    {
        $p = new MessagePersonalizer();
        $result = $p->personalize('Hello {name}!', [
            'FirstName' => 'Kiro',
            'SecondName' => 'Amgad',
            'ThirdName' => 'Test',
        ]);

        $this->assertSame('Hello Kiro Amgad Test!', $result['message']);
        $this->assertSame([], $result['missing']);
        $this->assertFalse($result['skipped']);
    }

    public function test_fallback_when_name_missing(): void
    {
        $p = new MessagePersonalizer();
        $result = $p->personalize('Hi {name}', [], 'fallback', 'صديقنا');

        $this->assertSame('Hi صديقنا', $result['message']);
        $this->assertContains('name', $result['missing']);
    }

    public function test_skip_when_name_missing(): void
    {
        $p = new MessagePersonalizer();
        $result = $p->personalize('Hi {name}', [], 'skip');

        $this->assertTrue($result['skipped']);
        $this->assertSame('', $result['message']);
    }

    public function test_empty_behavior(): void
    {
        $p = new MessagePersonalizer();
        $result = $p->personalize('Hi {name}!', [], 'empty');

        $this->assertSame('Hi !', $result['message']);
        $this->assertFalse($result['skipped']);
    }
}
