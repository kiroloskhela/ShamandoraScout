<?php

namespace App\Domain\EventFinance;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SeasonEventBookingPaymentService
{
    public function __construct(
        private readonly SeasonEventBookingService $bookings,
    ) {}

    public function shouldBypassLastInstallmentCompletion(?int $personId, ?string $specialCaseType): bool
    {
        return ($personId ? $this->bookings->isSpecialCase($personId) : false)
            || $specialCaseType === 'AKHOH_RAB';
    }

    /**
     * @return array{next_installment_number:int,is_last_installment:bool,force_full_last_installment:bool,amount:float,notes:?string}
     */
    public function calculateInstallment(object $booking, int $paymentsCount, float $requestedAmount, ?string $notes = null): array
    {
        $nextInstallmentNumber = $paymentsCount + 1;
        $isLastInstallment = $nextInstallmentNumber >= (int) $booking->InstallmentsNumber;
        $isSpecialBehavior = $this->shouldBypassLastInstallmentCompletion(
            isset($booking->PersonID) ? (int) $booking->PersonID : null,
            $booking->SpecialCaseType ?? null,
        );
        $forceFullLastInstallment = $isLastInstallment && ! $isSpecialBehavior;
        $amount = $forceFullLastInstallment ? (float) $booking->RemainingAmount : $requestedAmount;

        return [
            'next_installment_number' => $nextInstallmentNumber,
            'is_last_installment' => $isLastInstallment,
            'force_full_last_installment' => $forceFullLastInstallment,
            'amount' => $amount,
            'notes' => $forceFullLastInstallment
                ? trim(($notes ? $notes.' | ' : '').'آخر قسط - تم تحصيل كامل المتبقي تلقائيًا')
                : $notes,
        ];
    }

    /**
     * @return array{amount_paid:float,remaining_amount:float}
     */
    public function totalsAfterPayment(object $booking, float $amount): array
    {
        $newPaid = (float) $booking->AmountPaid + $amount;

        return [
            'amount_paid' => $newPaid,
            'remaining_amount' => max(0, (float) $booking->FinalRequiredAmount - $newPaid),
        ];
    }

    /**
     * @return array{amount_paid:float,remaining_amount:float}
     */
    public function totalsAfterPaymentEdit(float $finalRequiredAmount, float $otherPaymentsTotal, float $newAmount): array
    {
        $newPaid = $otherPaymentsTotal + $newAmount;

        return [
            'amount_paid' => $newPaid,
            'remaining_amount' => max(0, $finalRequiredAmount - $newPaid),
        ];
    }

    /**
     * @return array{refund_amount:float,amount_paid:float,remaining_amount:float,is_refunded:int}
     */
    public function fullRefundTotals(object $booking): array
    {
        return [
            'refund_amount' => (float) $booking->AmountPaid,
            'amount_paid' => 0.0,
            'remaining_amount' => (float) $booking->FinalRequiredAmount,
            'is_refunded' => 1,
        ];
    }

    /**
     * @return array{refund_amount:float,deduction_amount:float,amount_paid:float,remaining_amount:float,is_refunded:int,notes:string}
     */
    public function partialRefundTotals(object $booking, float $deductionAmount, ?string $notes = null): array
    {
        $amountPaid = (float) $booking->AmountPaid;

        if ($deductionAmount > $amountPaid) {
            throw new InvalidArgumentException('Deduction cannot exceed amount paid.');
        }

        $refundAmount = $amountPaid - $deductionAmount;

        return [
            'refund_amount' => $refundAmount,
            'deduction_amount' => $deductionAmount,
            'amount_paid' => $deductionAmount,
            'remaining_amount' => 0.0,
            'is_refunded' => 1,
            'notes' => 'استرداد مع خصم جزء | المدفوع: '.number_format($amountPaid, 2).
                ' | المخصوم: '.number_format($deductionAmount, 2).
                ' | المسترد: '.number_format($refundAmount, 2).
                ($notes ? ' | '.$notes : ''),
        ];
    }

    public function recordInstallment(object $booking, int $bookingId, int $serventId, array $installment): int
    {
        return (int) DB::transaction(function () use ($booking, $bookingId, $serventId, $installment) {
            $paymentId = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
                'SeasonEventParticipantFinanceID' => $bookingId,
                'ServentID' => $serventId,
                'PaymentDate' => now(),
                'Amount' => $installment['amount'],
                'InstallmentNumber' => $installment['next_installment_number'],
                'PaymentType' => 'PAYMENT',
                'Notes' => $installment['notes'],
            ]);

            $totals = $this->totalsAfterPayment($booking, (float) $installment['amount']);

            DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventParticipantFinanceID', $bookingId)
                ->update([
                    'AmountPaid' => $totals['amount_paid'],
                    'RemainingAmount' => $totals['remaining_amount'],
                ]);

            $this->createReceipt($paymentId, $serventId);

            return $paymentId;
        });
    }

    public function updateLastPaymentAmount(int $paymentId, int $bookingId, float $newAmount, float $finalRequiredAmount, float $otherPaymentsTotal, ?string $existingNotes): void
    {
        DB::transaction(function () use ($paymentId, $bookingId, $newAmount, $finalRequiredAmount, $otherPaymentsTotal, $existingNotes) {
            DB::table('SeasonEventParticipantFinancePayment')
                ->where('PaymentID', $paymentId)
                ->update([
                    'Amount' => $newAmount,
                    'Notes' => trim(($existingNotes ? $existingNotes.' | ' : '').'تم تعديل مبلغ آخر دفعة'),
                ]);

            $totals = $this->totalsAfterPaymentEdit($finalRequiredAmount, $otherPaymentsTotal, $newAmount);

            DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventParticipantFinanceID', $bookingId)
                ->update([
                    'AmountPaid' => $totals['amount_paid'],
                    'RemainingAmount' => $totals['remaining_amount'],
                ]);
        });
    }

    public function refundFull(int $bookingId, object $booking, int $serventId, int $installmentNumber): int
    {
        return (int) DB::transaction(function () use ($bookingId, $booking, $serventId, $installmentNumber) {
            $totals = $this->fullRefundTotals($booking);
            $paymentId = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
                'SeasonEventParticipantFinanceID' => $bookingId,
                'ServentID' => $serventId,
                'PaymentDate' => now(),
                'Amount' => $totals['refund_amount'],
                'InstallmentNumber' => $installmentNumber,
                'PaymentType' => 'REFUND',
                'Notes' => 'استرداد كامل لكل المبلغ المدفوع',
            ]);

            DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventParticipantFinanceID', $bookingId)
                ->update([
                    'IsRefunded' => $totals['is_refunded'],
                    'RefundDate' => now(),
                    'RemainingAmount' => $totals['remaining_amount'],
                    'AmountPaid' => $totals['amount_paid'],
                ]);

            $this->createReceipt($paymentId, $serventId);

            return $paymentId;
        });
    }

    public function refundPartial(int $bookingId, object $booking, int $serventId, int $installmentNumber, float $deductionAmount, ?string $notes = null): int
    {
        return (int) DB::transaction(function () use ($bookingId, $booking, $serventId, $installmentNumber, $deductionAmount, $notes) {
            $totals = $this->partialRefundTotals($booking, $deductionAmount, $notes);
            $paymentId = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
                'SeasonEventParticipantFinanceID' => $bookingId,
                'ServentID' => $serventId,
                'PaymentDate' => now(),
                'Amount' => $totals['refund_amount'],
                'InstallmentNumber' => $installmentNumber,
                'PaymentType' => 'REFUND',
                'Notes' => $totals['notes'],
            ]);

            DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventParticipantFinanceID', $bookingId)
                ->update([
                    'IsRefunded' => $totals['is_refunded'],
                    'RefundDate' => now(),
                    'AmountPaid' => $totals['amount_paid'],
                    'RemainingAmount' => $totals['remaining_amount'],
                ]);

            $this->createReceipt($paymentId, $serventId);

            return $paymentId;
        });
    }

    private function createReceipt(int $paymentId, int $serventId): void
    {
        $issuedAt = Carbon::now();
        $receiptId = DB::table('SeasonEventParticipantFinanceReceipt')->insertGetId([
            'PaymentID' => $paymentId,
            'ReceiptNumber' => 'TEMP',
            'IssuedAt' => $issuedAt,
            'IssuedByServentID' => $serventId,
        ]);

        DB::table('SeasonEventParticipantFinanceReceipt')
            ->where('ReceiptID', $receiptId)
            ->update([
                'ReceiptNumber' => 'REC-'.$issuedAt->format('i-H-d-m-y').'-'.$receiptId,
            ]);
    }
}
