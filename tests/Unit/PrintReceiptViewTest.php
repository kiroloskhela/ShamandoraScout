<?php

namespace Tests\Unit;

use Tests\TestCase;

class PrintReceiptViewTest extends TestCase
{
    private function receipt(): object
    {
        return (object) [
            'PaymentType' => 'PAY',
            'ReceiptNumber' => 'REC-1',
            'IssuedAt' => '2026-08-25 22:27:13',
            'SeasonEventID' => 87,
            'SeasonName' => '2026',
            'SeasonYear' => '2026',
            'EventTypeName' => 'Games Complex',
            'EventName' => 'Games Complex 2026',
            'PersonFullName' => 'Test Person',
            'PersonID' => 7,
            'PersonPersonalMobileNumber' => '01000485402',
            'ServentFullName' => 'Leader',
            'InstallmentNumber' => 1,
            'PaymentDate' => '2026-08-25 22:27:13',
            'InstallmentsNumber' => 1,
            'Amount' => 200,
            'OriginalPrice' => 200,
            'DiscountAmount' => 0,
            'FinalRequiredAmount' => 200,
            'AmountPaid' => 200,
            'RemainingAmount' => 0,
        ];
    }

    private function render(?string $qrPng, ?string $qrPayload): string
    {
        return view('event_booking_finance.print_receipt', [
            'fileName' => 'Receipt.pdf',
            'receipt' => $this->receipt(),
            'qrPng' => $qrPng,
            'qrPayload' => $qrPayload,
        ])->render();
    }

    public function test_both_copies_include_attendance_qr_image_and_payload(): void
    {
        $html = $this->render(null, 'SHAM:7');

        $this->assertSame(2, substr_count($html, 'data-qr="SHAM:7"'));
        $this->assertSame(2, substr_count($html, 'class="js-qr"'));
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'SHAM:7'));
        $this->assertSame(2, substr_count($html, 'class="qr-box"'));
        $this->assertStringContainsString('qrcode-generator@1.4.4', $html);
    }

    public function test_payload_renders_on_both_copies_when_image_is_missing(): void
    {
        $html = $this->render(null, 'GUEST:7');

        $this->assertSame(0, substr_count($html, 'data:image/png;base64,'));
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'GUEST:7'));
        $this->assertSame(2, substr_count($html, 'class="qr-box"'));
    }

    public function test_hides_qr_when_payload_is_missing(): void
    {
        $html = $this->render(null, null);

        $this->assertSame(0, substr_count($html, 'class="qr-box"'));
        $this->assertSame(0, substr_count($html, 'data:image/png;base64,'));
    }

    public function test_back_links_to_event_bookings_index(): void
    {
        $html = $this->render(null, null);

        $this->assertStringContainsString('event-booking-finance/event/87', $html);
        $this->assertStringNotContainsString('window.history.back()', $html);
        $this->assertStringContainsString('eventIndexUrl', $html);
    }
}
