<?php

namespace Tests\Unit;

use App\Services\AttendanceQrService;
use App\Services\WhatsAppBridgeClient;
use Tests\TestCase;

class AttendanceQrServiceTest extends TestCase
{
    private function service(): AttendanceQrService
    {
        return new AttendanceQrService($this->createMock(WhatsAppBridgeClient::class));
    }

    public function test_payload_and_parse_person_id(): void
    {
        $qr = $this->service();

        $this->assertSame('SHAM:42', $qr->payloadForPerson(42));
        $this->assertSame(42, $qr->parsePersonId('SHAM:42'));
        $this->assertSame(42, $qr->parsePersonId('42'));
        $this->assertSame(42, $qr->parsePersonId('  SHAM:42  '));
        $this->assertNull($qr->parsePersonId(''));
        $this->assertNull($qr->parsePersonId('abc'));
    }

    public function test_png_base64_is_non_empty(): void
    {
        $png = $this->service()->pngBase64(1);

        $this->assertNotSame('', $png);
        $this->assertNotFalse(base64_decode($png, true));
    }
}
