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

    public function test_payload_and_parse_entity_codes(): void
    {
        $qr = $this->service();

        $this->assertSame('SHAM:42', $qr->payloadForPerson(42));
        $this->assertSame('GUEST:7', $qr->payloadForEntity(AttendanceQrService::TYPE_GUEST, 7));
        $this->assertSame('FAM:9', $qr->payloadForEntity(AttendanceQrService::TYPE_FAMILY, 9));

        $this->assertSame(['type' => 'PERSON', 'id' => 42], $qr->parseCode('SHAM:42'));
        $this->assertSame(['type' => 'GUEST', 'id' => 7], $qr->parseCode('GUEST:7'));
        $this->assertSame(['type' => 'FAMILY', 'id' => 9], $qr->parseCode('FAM:9'));
        $this->assertSame(['type' => 'PERSON', 'id' => 42], $qr->parseCode('42'));
        $this->assertSame(42, $qr->parsePersonId('SHAM:42'));
        $this->assertNull($qr->parsePersonId('GUEST:7'));
        $this->assertNull($qr->parseCode(''));
        $this->assertNull($qr->parseCode('abc'));
    }

    public function test_png_base64_is_non_empty(): void
    {
        $png = $this->service()->pngBase64ForEntity(AttendanceQrService::TYPE_GUEST, 1);

        $this->assertNotSame('', $png);
        $this->assertNotFalse(base64_decode($png, true));
    }

    public function test_whatsapp_qr_send_is_disabled_by_default(): void
    {
        $this->assertFalse($this->service()->shouldSendViaWhatsApp());
    }
}
