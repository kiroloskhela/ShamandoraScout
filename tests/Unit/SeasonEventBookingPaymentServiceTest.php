<?php

namespace Tests\Unit;

use App\Domain\EventFinance\SeasonEventBookingPaymentService;
use App\Domain\EventFinance\SeasonEventBookingService;
use InvalidArgumentException;
use Tests\TestCase;

class SeasonEventBookingPaymentServiceTest extends TestCase
{
    private SeasonEventBookingPaymentService $payments;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payments = new SeasonEventBookingPaymentService(new SeasonEventBookingService());
    }

    public function test_last_regular_installment_forces_remaining_amount(): void
    {
        $booking = (object) [
            'PersonID' => null,
            'SpecialCaseType' => 'NONE',
            'InstallmentsNumber' => 3,
            'RemainingAmount' => 125.50,
        ];

        $result = $this->payments->calculateInstallment($booking, 2, 20.0, 'cash');

        $this->assertTrue($result['is_last_installment']);
        $this->assertTrue($result['force_full_last_installment']);
        $this->assertSame(125.50, $result['amount']);
        $this->assertStringContainsString('cash |', $result['notes']);
    }

    public function test_special_case_last_installment_keeps_requested_amount(): void
    {
        $booking = (object) [
            'PersonID' => null,
            'SpecialCaseType' => 'AKHOH_RAB',
            'InstallmentsNumber' => 3,
            'RemainingAmount' => 125.50,
        ];

        $result = $this->payments->calculateInstallment($booking, 2, 20.0, null);

        $this->assertTrue($result['is_last_installment']);
        $this->assertFalse($result['force_full_last_installment']);
        $this->assertSame(20.0, $result['amount']);
        $this->assertNull($result['notes']);
    }

    public function test_partial_refund_totals_keep_deduction_as_paid_amount(): void
    {
        $booking = (object) [
            'AmountPaid' => 300.0,
        ];

        $result = $this->payments->partialRefundTotals($booking, 75.0, 'admin note');

        $this->assertSame(225.0, $result['refund_amount']);
        $this->assertSame(75.0, $result['amount_paid']);
        $this->assertSame(0.0, $result['remaining_amount']);
        $this->assertSame(1, $result['is_refunded']);
        $this->assertStringContainsString('admin note', $result['notes']);
    }

    public function test_partial_refund_rejects_deduction_above_paid_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->payments->partialRefundTotals((object) ['AmountPaid' => 50.0], 75.0);
    }
}
