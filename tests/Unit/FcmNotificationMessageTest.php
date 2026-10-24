<?php

namespace Tests\Unit;

use App\Jobs\SendFcmNotification;
use App\Services\FcmService;
use PHPUnit\Framework\TestCase;

class FcmNotificationMessageTest extends TestCase
{
    public function test_collapse_key_is_sent_to_android_and_apple(): void
    {
        $message = FcmService::notificationMessage('Hello', 'Body', 'abc123');

        $this->assertSame('abc123', $message['android']['collapse_key']);
        $this->assertSame('abc123', $message['apns']['headers']['apns-collapse-id']);
    }

    public function test_job_keeps_the_same_collapse_key_for_retries(): void
    {
        $job = new SendFcmNotification(['token-a'], 'Hello', 'Body');
        $restored = unserialize(serialize($job));

        $this->assertSame(32, strlen($job->collapseKey));
        $this->assertSame($job->collapseKey, $restored->collapseKey);
    }
}
