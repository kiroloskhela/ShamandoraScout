<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AttendanceQrService
{
    public const TYPE_PERSON = 'PERSON';

    public const TYPE_GUEST = 'GUEST';

    public const TYPE_FAMILY = 'FAMILY';

    public const PREFIX_PERSON = 'SHAM:';

    public const PREFIX_GUEST = 'GUEST:';

    public const PREFIX_FAMILY = 'FAM:';

    public function __construct(
        private readonly WhatsAppBridgeClient $whatsApp,
    ) {}

    public function payloadForPerson(int $personId): string
    {
        return $this->payloadForEntity(self::TYPE_PERSON, $personId);
    }

    public function payloadForEntity(string $type, int $id): string
    {
        return match (strtoupper($type)) {
            self::TYPE_GUEST => self::PREFIX_GUEST.$id,
            self::TYPE_FAMILY => self::PREFIX_FAMILY.$id,
            default => self::PREFIX_PERSON.$id,
        };
    }

    /**
     * @return array{type: string, id: int}|null
     */
    public function parseCode(string $raw): ?array
    {
        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(SHAM|GUEST|FAM)\s*:\s*(\d+)\s*$/i', $value, $m)) {
            $prefix = strtoupper($m[1]);
            $id = (int) $m[2];
            if ($id <= 0) {
                return null;
            }

            return [
                'type' => match ($prefix) {
                    'GUEST' => self::TYPE_GUEST,
                    'FAM' => self::TYPE_FAMILY,
                    default => self::TYPE_PERSON,
                },
                'id' => $id,
            ];
        }

        // Bare numeric ID = person (legacy attendance-only QR)
        if (preg_match('/^\d+$/', $value)) {
            $id = (int) $value;

            return $id > 0 ? ['type' => self::TYPE_PERSON, 'id' => $id] : null;
        }

        if (preg_match('/(\d+)\s*$/', $value, $m)) {
            $id = (int) $m[1];

            return $id > 0 ? ['type' => self::TYPE_PERSON, 'id' => $id] : null;
        }

        return null;
    }

    public function parsePersonId(string $raw): ?int
    {
        $parsed = $this->parseCode($raw);
        if (! $parsed || $parsed['type'] !== self::TYPE_PERSON) {
            return null;
        }

        return $parsed['id'];
    }

    public function pngBase64ForEntity(string $type, int $id): string
    {
        $result = Builder::create()
            ->writer(new PngWriter)
            ->data($this->payloadForEntity($type, $id))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(420)
            ->margin(16)
            ->build();

        return base64_encode($result->getString());
    }

    public function pngBase64(int $personId): string
    {
        return $this->pngBase64ForEntity(self::TYPE_PERSON, $personId);
    }

    /**
     * @return array{PersonID: int, PersonName: string, PhoneNumber: string, QetaaName: string, SanaMarhalaName: string}|null
     */
    public function personCard(int $personId): ?array
    {
        $card = $this->entityCard(self::TYPE_PERSON, $personId);

        if (! $card) {
            return null;
        }

        return [
            'PersonID' => $card['EntityID'],
            'PersonName' => $card['EntityName'],
            'PhoneNumber' => $card['PhoneNumber'],
            'QetaaName' => $card['QetaaName'],
            'SanaMarhalaName' => $card['SanaMarhalaName'],
        ];
    }

    /**
     * @return array{
     *   EntityType: string,
     *   EntityID: int,
     *   EntityName: string,
     *   PhoneNumber: string,
     *   QetaaName: string,
     *   SanaMarhalaName: string,
     *   BookingTypeLabel: string
     * }|null
     */
    public function entityCard(string $type, int $id): ?array
    {
        $type = strtoupper($type);

        return match ($type) {
            self::TYPE_GUEST => $this->guestCard($id),
            self::TYPE_FAMILY => $this->familyCard($id),
            default => $this->personEntityCard($id),
        };
    }

    /**
     * @return array{ok: true, to: mixed, messageId: mixed}
     */
    public function shouldSendViaWhatsApp(): bool
    {
        return (bool) config('services.whatsapp.send_qr');
    }

    public function sendQrViaWhatsApp(int $personId, ?string $eventName = null): array
    {
        return $this->sendEntityQrViaWhatsApp(self::TYPE_PERSON, $personId, $eventName);
    }

    /**
     * @return array{ok: true, to: mixed, messageId: mixed}
     */
    public function sendEntityQrViaWhatsApp(string $type, int $id, ?string $eventName = null): array
    {
        if (! $this->shouldSendViaWhatsApp()) {
            throw new RuntimeException(__('WhatsApp QR sending is temporarily disabled.'));
        }

        $card = $this->entityCard($type, $id);
        if (! $card) {
            throw new RuntimeException(__('Person not found.'));
        }

        if ($card['PhoneNumber'] === '') {
            throw new RuntimeException(__('No personal mobile number for this person.'));
        }

        $name = $card['EntityName'] ?: ('#'.$id);
        $caption = __('attendance_qr_whatsapp_greeting', ['name' => $name]);

        if ($eventName) {
            $caption .= "\n".__('attendance_qr_whatsapp_event', ['event' => $eventName]);
        }

        $caption .= "\n".__('attendance_qr_whatsapp_code', [
            'code' => $this->payloadForEntity($type, $id),
        ]);

        $caption .= "\n\n".__('attendance_qr_whatsapp_closing');

        return $this->whatsApp->sendImage(
            $card['PhoneNumber'],
            $this->pngBase64ForEntity($type, $id),
            $caption,
            'image/png'
        );
    }

    public function eventTakesReservation(int $seasonEventId): bool
    {
        return (bool) DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->join('EventType as et', 'et.EventTypeID', '=', 'e.EventTypeID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->value('et.TakesReservation');
    }

    public function eventName(int $seasonEventId): ?string
    {
        return DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->value('e.EventName');
    }

    /**
     * @return array{type: string, id: int}|null
     */
    public function entityFromBooking(object $booking): ?array
    {
        if (! empty($booking->PersonID)) {
            return ['type' => self::TYPE_PERSON, 'id' => (int) $booking->PersonID];
        }
        if (! empty($booking->GuestID)) {
            return ['type' => self::TYPE_GUEST, 'id' => (int) $booking->GuestID];
        }
        if (! empty($booking->FamilyID)) {
            return ['type' => self::TYPE_FAMILY, 'id' => (int) $booking->FamilyID];
        }

        return null;
    }

    /**
     * @return array{
     *   EntityType: string,
     *   EntityID: int,
     *   EntityName: string,
     *   PhoneNumber: string,
     *   QetaaName: string,
     *   SanaMarhalaName: string,
     *   BookingTypeLabel: string
     * }|null
     */
    private function personEntityCard(int $personId): ?array
    {
        $person = DB::table('PersonInformation as p')
            ->leftJoin('PersonPhoneNumbers as ph', 'ph.PersonID', '=', 'p.PersonID')
            ->leftJoin('PersonSanaMarhala as psm', 'psm.PersonID', '=', 'p.PersonID')
            ->leftJoin('SanaMarhala as sm', 'sm.SanaMarhalaID', '=', 'psm.SanaMarhalaID')
            ->leftJoin('PersonQetaa as pq', 'pq.PersonID', '=', 'p.PersonID')
            ->leftJoin('Qetaa as q', 'q.QetaaID', '=', 'pq.QetaaID')
            ->where('p.PersonID', $personId)
            ->groupBy('p.PersonID', 'p.FirstName', 'p.SecondName', 'p.ThirdName', 'p.FourthName', 'sm.SanaMarhalaName')
            // MAX instead of MySQL GROUP_CONCAT ... SEPARATOR so sqlite tests can mark person bookings.
            ->selectRaw("
                p.PersonID,
                p.FirstName, p.SecondName, p.ThirdName, p.FourthName,
                COALESCE(MAX(ph.PersonPersonalMobileNumber), '') as PhoneNumber,
                COALESCE(MAX(q.QetaaName), '') as QetaaName,
                COALESCE(sm.SanaMarhalaName, '') as SanaMarhalaName
            ")
            ->first();

        if (! $person) {
            return null;
        }

        return [
            'EntityType' => self::TYPE_PERSON,
            'EntityID' => (int) $person->PersonID,
            'EntityName' => trim("{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}"),
            'PhoneNumber' => (string) $person->PhoneNumber,
            'QetaaName' => (string) $person->QetaaName,
            'SanaMarhalaName' => (string) $person->SanaMarhalaName,
            'BookingTypeLabel' => __('Person'),
        ];
    }

    /**
     * @return array{
     *   EntityType: string,
     *   EntityID: int,
     *   EntityName: string,
     *   PhoneNumber: string,
     *   QetaaName: string,
     *   SanaMarhalaName: string,
     *   BookingTypeLabel: string
     * }|null
     */
    private function guestCard(int $guestId): ?array
    {
        $guest = DB::table('Guests')->where('GuestID', $guestId)->first();
        if (! $guest) {
            return null;
        }

        return [
            'EntityType' => self::TYPE_GUEST,
            'EntityID' => (int) $guest->GuestID,
            'EntityName' => trim("{$guest->FirstName} {$guest->SecondName} {$guest->ThirdName} {$guest->FourthName}"),
            'PhoneNumber' => (string) ($guest->MobileNumber ?? ''),
            'QetaaName' => '',
            'SanaMarhalaName' => '',
            'BookingTypeLabel' => __('Guests'),
        ];
    }

    /**
     * @return array{
     *   EntityType: string,
     *   EntityID: int,
     *   EntityName: string,
     *   PhoneNumber: string,
     *   QetaaName: string,
     *   SanaMarhalaName: string,
     *   BookingTypeLabel: string
     * }|null
     */
    private function familyCard(int $familyId): ?array
    {
        $family = DB::table('FamilyMembers')->where('FamilyID', $familyId)->first();
        if (! $family) {
            return null;
        }

        return [
            'EntityType' => self::TYPE_FAMILY,
            'EntityID' => (int) $family->FamilyID,
            'EntityName' => trim("{$family->FirstName} {$family->SecondName} {$family->ThirdName} {$family->FourthName}"),
            'PhoneNumber' => (string) ($family->MobileNumber ?? ''),
            'QetaaName' => '',
            'SanaMarhalaName' => '',
            'BookingTypeLabel' => __('Families'),
        ];
    }
}
