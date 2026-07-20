<?php

namespace App\Domain\EventFinance;

use App\Jobs\SendAttendanceQrWhatsApp;
use App\Services\AttendanceQrService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Season-event booking finance domain (create booking + eligibility).
 */
class SeasonEventBookingService
{
    public function getEventInfo(int $seasonEventId): ?object
    {
        return DB::table('SeasonEvent as se')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->select(
                'se.SeasonEventID',
                'se.EventID',
                's.SeasonName',
                's.SeasonYear',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
                'et.EventTypeName',
                'et.TakesReservation'
            )
            ->first();
    }

    public function getFinancePlan(int $seasonEventId): ?object
    {
        return DB::table('SeasonEventFinance')->where('SeasonEventID', $seasonEventId)->first();
    }

    public function isBlacklisted(int $personId): bool
    {
        return DB::table('PersonBlackList')->where('PersonID', $personId)->exists();
    }

    public function isSpecialCase(int $personId): bool
    {
        return DB::table('PersonSpecialCase')->where('PersonID', $personId)->exists();
    }

    public function isEligibleByQetaa(int $seasonEventId, int $personId): bool
    {
        $event = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventId)->first();
        if (! $event) {
            return false;
        }

        return DB::table('EventQetaa as eq')
            ->join('PersonQetaa as pq', 'eq.QetaaID', '=', 'pq.QetaaID')
            ->where('eq.EventID', $event->EventID)
            ->where('pq.PersonID', $personId)
            ->exists();
    }

    /**
     * Create a booking + first payment + receipt.
     *
     * @param  array{
     *   booking_type: string,
     *   person_id?: int|null,
     *   guest_id?: int|null,
     *   family_id?: int|null,
     *   first_payment_date: string,
     *   first_payment_amount: float|int|string,
     *   is_not_able_to_pay_all?: mixed,
     *   special_case_type?: string|null,
     *   discount_amount?: float|int|string|null,
     *   special_case_note?: string|null,
     *   shirt_size?: string|null,
     *   notes?: string|null,
     * }  $payload
     * @return array{ok: bool, payment_id?: int, field?: string, message?: string}
     */
    public function createBooking(int $seasonEventId, array $payload, int $serventId): array
    {
        $plan = $this->getFinancePlan($seasonEventId);
        if (! $plan || ! $this->getEventInfo($seasonEventId)) {
            return ['ok' => false, 'field' => 'general', 'message' => 'الفعالية أو الخطة المالية غير موجودة.'];
        }

        $bookingType = (string) $payload['booking_type'];
        $personID = null;
        $guestID = null;
        $familyID = null;
        $entityField = null;
        $entityValue = null;

        if ($bookingType === 'PERSON') {
            $personID = (int) ($payload['person_id'] ?? 0);
            if ($personID <= 0) {
                return ['ok' => false, 'field' => 'person_id', 'message' => 'يجب اختيار الشخص.'];
            }
            $entityField = 'PersonID';
            $entityValue = $personID;
        } elseif ($bookingType === 'GUEST') {
            $guestID = (int) ($payload['guest_id'] ?? 0);
            if ($guestID <= 0) {
                return ['ok' => false, 'field' => 'guest_id', 'message' => 'يجب اختيار الضيف.'];
            }
            $entityField = 'GuestID';
            $entityValue = $guestID;
        } else {
            $familyID = (int) ($payload['family_id'] ?? 0);
            if ($familyID <= 0) {
                return ['ok' => false, 'field' => 'family_id', 'message' => 'يجب اختيار فرد العائلة.'];
            }
            $entityField = 'FamilyID';
            $entityValue = $familyID;
        }

        if ($bookingType === 'PERSON') {
            if ($this->isBlacklisted($personID)) {
                return ['ok' => false, 'field' => 'person_id', 'message' => 'هذا الشخص موجود في القائمة السوداء ولا يمكنه الحجز.'];
            }
            if (! $this->isEligibleByQetaa($seasonEventId, $personID)) {
                return ['ok' => false, 'field' => 'person_id', 'message' => 'هذا الشخص غير مؤهل لهذه الفعالية.'];
            }
        }

        $alreadyBooked = DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', $seasonEventId)
            ->where($entityField, $entityValue)
            ->exists();

        if ($alreadyBooked) {
            return ['ok' => false, 'field' => 'general', 'message' => 'هذا الحجز موجود بالفعل في هذه الفعالية.'];
        }

        $priceDate = Carbon::parse($payload['first_payment_date'])->format('Y-m-d');
        $paymentDateTime = now();

        $priceRow = DB::table('SeasonEventFinancePrice')
            ->where('SeasonEventID', $seasonEventId)
            ->where('StartDate', '<=', $priceDate)
            ->where('EndDate', '>=', $priceDate)
            ->orderBy('StartDate')
            ->first();

        if (! $priceRow) {
            return ['ok' => false, 'field' => 'first_payment_date', 'message' => 'لا يوجد سعر صالح في هذا التاريخ.'];
        }

        $isPermanentSpecial = $bookingType === 'PERSON' ? $this->isSpecialCase($personID) : false;
        $specialCaseType = ! empty($payload['is_not_able_to_pay_all'])
            ? ($payload['special_case_type'] ?? 'NONE')
            : 'NONE';

        $hasPersonSpecialCase = ($isPermanentSpecial || $specialCaseType === 'AKHOH_RAB') ? 1 : 0;
        $discountAmount = (float) ($payload['discount_amount'] ?? 0);
        $specialCaseNote = $payload['special_case_note'] ?? null;
        $originalPrice = (float) $priceRow->Price;
        $finalRequiredAmount = max(0, $originalPrice - $discountAmount);
        $firstPaymentAmount = (float) $payload['first_payment_amount'];
        $installmentsNumber = (int) $plan->MaxInstallmentsNumber;

        if ($finalRequiredAmount <= 0) {
            return ['ok' => false, 'field' => 'discount_amount', 'message' => 'المبلغ النهائي المطلوب يجب أن يكون أكبر من صفر.'];
        }

        if ($firstPaymentAmount > $finalRequiredAmount) {
            return ['ok' => false, 'field' => 'first_payment_amount', 'message' => 'لا يمكن أن تكون أول دفعة أكبر من المبلغ المطلوب النهائي.'];
        }

        $isSpecialBehavior = $isPermanentSpecial || $specialCaseType === 'AKHOH_RAB';

        if (! $isSpecialBehavior && (int) $plan->AllowBelowMinimumDeposit === 0 && $firstPaymentAmount < (float) $plan->MinimumDeposit) {
            return ['ok' => false, 'field' => 'first_payment_amount', 'message' => 'لا يمكن أن تكون أول دفعة أقل من الحد الأدنى للمقدم.'];
        }

        if (($specialCaseType === 'HAS_BROTHERS' || $specialCaseType === 'OTHER') && $discountAmount <= 0) {
            return ['ok' => false, 'field' => 'discount_amount', 'message' => 'يجب إدخال مبلغ خصم أكبر من صفر.'];
        }

        if ((int) $plan->MaxInstallmentsNumber === 1 && $firstPaymentAmount != $finalRequiredAmount) {
            return ['ok' => false, 'field' => 'first_payment_amount', 'message' => 'هذه الفعالية تحتوي على قسط واحد فقط، لذلك يجب دفع كامل المبلغ في أول دفعة.'];
        }

        try {
            $result = DB::transaction(function () use (
                $seasonEventId,
                $personID,
                $guestID,
                $familyID,
                $serventId,
                $paymentDateTime,
                $originalPrice,
                $discountAmount,
                $finalRequiredAmount,
                $specialCaseType,
                $specialCaseNote,
                $hasPersonSpecialCase,
                $installmentsNumber,
                $firstPaymentAmount,
                $payload
            ) {
                $bookingID = DB::table('SeasonEventParticipantFinance')->insertGetId([
                    'SeasonEventID' => $seasonEventId,
                    'PersonID' => $personID,
                    'GuestID' => $guestID,
                    'FamilyID' => $familyID,
                    'ServentID' => $serventId,
                    'FirstPaymentDate' => $paymentDateTime->format('Y-m-d H:i:s'),
                    'OriginalPrice' => $originalPrice,
                    'DiscountAmount' => $discountAmount,
                    'FinalRequiredAmount' => $finalRequiredAmount,
                    'SpecialCaseType' => $specialCaseType,
                    'SpecialCaseNote' => $specialCaseNote,
                    'HasPersonSpecialCase' => $hasPersonSpecialCase,
                    'LockedPrice' => $finalRequiredAmount,
                    'IsRefunded' => 0,
                    'RefundDate' => null,
                    'InstallmentsNumber' => $installmentsNumber,
                    'AmountPaid' => $firstPaymentAmount,
                    'RemainingAmount' => max(0, $finalRequiredAmount - $firstPaymentAmount),
                    'ShirtSize' => $payload['shirt_size'] ?? null,
                    'Notes' => $payload['notes'] ?? null,
                ]);

                $paymentID = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
                    'SeasonEventParticipantFinanceID' => $bookingID,
                    'ServentID' => $serventId,
                    'PaymentDate' => $paymentDateTime,
                    'Amount' => $firstPaymentAmount,
                    'InstallmentNumber' => 1,
                    'PaymentType' => 'PAYMENT',
                    'Notes' => 'أول دفعة',
                ]);

                $receiptID = DB::table('SeasonEventParticipantFinanceReceipt')->insertGetId([
                    'PaymentID' => $paymentID,
                    'ReceiptNumber' => 'TEMP',
                    'IssuedAt' => now(),
                    'IssuedByServentID' => $serventId,
                ]);

                DB::table('SeasonEventParticipantFinanceReceipt')
                    ->where('ReceiptID', $receiptID)
                    ->update([
                        'ReceiptNumber' => 'REC-'.now()->format('i-H-d-m-y').'-'.$receiptID,
                    ]);

                return [
                    'payment_id' => (int) $paymentID,
                    'booking_id' => (int) $bookingID,
                ];
            });

            $this->queueAttendanceQrIfReservation(
                $seasonEventId,
                $personID,
                $guestID,
                $familyID
            );

            return [
                'ok' => true,
                'payment_id' => $result['payment_id'],
                'booking_id' => $result['booking_id'],
            ];
        } catch (Throwable $e) {
            report($e);

            return ['ok' => false, 'field' => 'general', 'message' => 'حدث خطأ أثناء إنشاء الحجز.'];
        }
    }

    private function queueAttendanceQrIfReservation(
        int $seasonEventId,
        ?int $personID,
        ?int $guestID,
        ?int $familyID,
    ): void {
        try {
            $event = $this->getEventInfo($seasonEventId);
            if (! $event || empty($event->TakesReservation)) {
                return;
            }

            $sendQr = DB::table('SeasonEventFinance')
                ->where('SeasonEventID', $seasonEventId)
                ->value('SendQrWhatsApp');

            if (empty($sendQr)) {
                return;
            }

            $entityType = null;
            $entityId = null;
            if ($personID) {
                $entityType = AttendanceQrService::TYPE_PERSON;
                $entityId = $personID;
            } elseif ($guestID) {
                $entityType = AttendanceQrService::TYPE_GUEST;
                $entityId = $guestID;
            } elseif ($familyID) {
                $entityType = AttendanceQrService::TYPE_FAMILY;
                $entityId = $familyID;
            }

            if (! $entityType || ! $entityId) {
                return;
            }

            SendAttendanceQrWhatsApp::dispatch($entityType, $entityId, (string) $event->EventName);
        } catch (Throwable $e) {
            Log::warning('Failed to queue attendance QR after booking', [
                'seasonEventId' => $seasonEventId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getBookingDetails(int $bookingId): ?object
    {
        return DB::table('SeasonEventParticipantFinance as b')
            ->join('SeasonEvent as se', 'b.SeasonEventID', '=', 'se.SeasonEventID')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->leftJoin('PersonInformation as p', 'b.PersonID', '=', 'p.PersonID')
            ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
            ->leftJoin('Guests as g', 'b.GuestID', '=', 'g.GuestID')
            ->leftJoin('FamilyMembers as f', 'b.FamilyID', '=', 'f.FamilyID')
            ->where('b.SeasonEventParticipantFinanceID', $bookingId)
            ->select(
                'b.*',
                DB::raw("
                TRIM(CONCAT(
                    COALESCE(p.FirstName, g.FirstName, f.FirstName, ''), ' ',
                    COALESCE(p.SecondName, g.SecondName, f.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, g.ThirdName, f.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, g.FourthName, f.FourthName, '')
                )) as PersonFullName
            "),
                DB::raw("COALESCE(ppn.PersonPersonalMobileNumber, g.MobileNumber, f.MobileNumber, '-') as PersonPersonalMobileNumber"),
                DB::raw("
                CASE
                    WHEN b.PersonID IS NOT NULL THEN CONCAT('SH-', b.PersonID)
                    WHEN b.FamilyID IS NOT NULL THEN CONCAT('FM-', b.FamilyID)
                    WHEN b.GuestID IS NOT NULL THEN CONCAT('GU-', b.GuestID)
                    ELSE '-'
                END as BookingCode
            "),
                DB::raw("
                CASE
                    WHEN b.PersonID IS NOT NULL THEN 'شخص'
                    WHEN b.FamilyID IS NOT NULL THEN 'اهالي'
                    WHEN b.GuestID IS NOT NULL THEN 'ضيوف'
                    ELSE '-'
                END as BookingEntityLabel
            "),
                's.SeasonName',
                's.SeasonYear',
                'e.EventName',
                'et.EventTypeName'
            )
            ->first();
    }

    public function countPayments(int $bookingId): int
    {
        return (int) DB::table('SeasonEventParticipantFinancePayment')
            ->where('SeasonEventParticipantFinanceID', $bookingId)
            ->where('PaymentType', 'PAYMENT')
            ->count();
    }

    public function getPaymentWithBooking(int $paymentId): ?object
    {
        return DB::table('SeasonEventParticipantFinancePayment as p')
            ->join('SeasonEventParticipantFinance as b', 'p.SeasonEventParticipantFinanceID', '=', 'b.SeasonEventParticipantFinanceID')
            ->where('p.PaymentID', $paymentId)
            ->select(
                'p.*',
                'b.SeasonEventID',
                'b.FinalRequiredAmount',
                'b.InstallmentsNumber',
                'b.PersonID',
                'b.GuestID',
                'b.FamilyID'
            )
            ->first();
    }

    public function isLastPayment(int $bookingId, int $paymentId): bool
    {
        $lastPayment = DB::table('SeasonEventParticipantFinancePayment')
            ->where('SeasonEventParticipantFinanceID', $bookingId)
            ->orderByDesc('PaymentDate')
            ->orderByDesc('PaymentID')
            ->first();

        return $lastPayment && (int) $lastPayment->PaymentID === $paymentId;
    }

    /**
     * Hard-delete a booking and all related payment/receipt/attendance rows.
     * Payments and receipts cascade via DB FKs; attendance has no FK and is removed explicitly.
     *
     * @return object|null The deleted booking row (for redirect context), or null if missing.
     */
    public function deleteBooking(int $bookingId): ?object
    {
        $booking = DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventParticipantFinanceID', $bookingId)
            ->first();

        if (! $booking) {
            return null;
        }

        DB::transaction(function () use ($bookingId) {
            if (DB::getSchemaBuilder()->hasTable('SeasonEventBookingAttendance')) {
                DB::table('SeasonEventBookingAttendance')
                    ->where('SeasonEventParticipantFinanceID', $bookingId)
                    ->delete();
            }

            DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventParticipantFinanceID', $bookingId)
                ->delete();
        });

        return $booking;
    }
}
