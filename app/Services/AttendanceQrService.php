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
    public const PAYLOAD_PREFIX = 'SHAM:';

    public function __construct(
        private readonly WhatsAppBridgeClient $whatsApp,
    ) {}

    public function payloadForPerson(int $personId): string
    {
        return self::PAYLOAD_PREFIX.$personId;
    }

    public function parsePersonId(string $raw): ?int
    {
        $value = trim($raw);
        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, self::PAYLOAD_PREFIX)) {
            $value = substr($value, strlen(self::PAYLOAD_PREFIX));
        }

        // Accept bare PersonID or URLs ending with digits
        if (preg_match('/(\d+)\s*$/', $value, $m)) {
            $id = (int) $m[1];

            return $id > 0 ? $id : null;
        }

        return null;
    }

    public function pngBase64(int $personId): string
    {
        $result = Builder::create()
            ->writer(new PngWriter)
            ->data($this->payloadForPerson($personId))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(420)
            ->margin(16)
            ->build();

        return base64_encode($result->getString());
    }

    /**
     * @return array{PersonID: int, PersonName: string, PhoneNumber: string, QetaaName: string, SanaMarhalaName: string}|null
     */
    public function personCard(int $personId): ?array
    {
        $person = DB::table('PersonInformation as p')
            ->leftJoin('PersonPhoneNumbers as ph', 'ph.PersonID', '=', 'p.PersonID')
            ->leftJoin('PersonSanaMarhala as psm', 'psm.PersonID', '=', 'p.PersonID')
            ->leftJoin('SanaMarhala as sm', 'sm.SanaMarhalaID', '=', 'psm.SanaMarhalaID')
            ->leftJoin('PersonQetaa as pq', 'pq.PersonID', '=', 'p.PersonID')
            ->leftJoin('Qetaa as q', 'q.QetaaID', '=', 'pq.QetaaID')
            ->where('p.PersonID', $personId)
            ->groupBy('p.PersonID', 'p.FirstName', 'p.SecondName', 'p.ThirdName', 'p.FourthName', 'sm.SanaMarhalaName')
            ->selectRaw("
                p.PersonID,
                p.FirstName, p.SecondName, p.ThirdName, p.FourthName,
                COALESCE(MAX(ph.PersonPersonalMobileNumber), '') as PhoneNumber,
                COALESCE(GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ', '), '') as QetaaName,
                COALESCE(sm.SanaMarhalaName, '') as SanaMarhalaName
            ")
            ->first();

        if (! $person) {
            return null;
        }

        return [
            'PersonID' => (int) $person->PersonID,
            'PersonName' => trim("{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}"),
            'PhoneNumber' => (string) $person->PhoneNumber,
            'QetaaName' => (string) $person->QetaaName,
            'SanaMarhalaName' => (string) $person->SanaMarhalaName,
        ];
    }

    /**
     * @return array{ok: true, to: mixed, messageId: mixed}
     */
    public function sendQrViaWhatsApp(int $personId, ?string $eventName = null): array
    {
        $card = $this->personCard($personId);
        if (! $card) {
            throw new RuntimeException(__('Person not found.'));
        }

        if ($card['PhoneNumber'] === '') {
            throw new RuntimeException(__('No personal mobile number for this person.'));
        }

        $caption = __('Hello :name, this is your Shamandora Scout attendance QR code. Show it at the event entrance.', [
            'name' => $card['PersonName'] ?: ('#'.$personId),
        ]);

        if ($eventName) {
            $caption .= "\n".__('Event').': '.$eventName;
        }

        $caption .= "\n".__('Person ID').': '.$personId;

        return $this->whatsApp->sendImage(
            $card['PhoneNumber'],
            $this->pngBase64($personId),
            $caption,
            'image/png'
        );
    }
}
