<?php

namespace App\Domain\Enrolment;

/**
 * Maps sana marhala + gender (+ leaders school) to scout sectors for liveform step 1.
 */
class LiveFormQetaaResolver
{
    public const QETAA_BARAEM = 1;

    public const QETAA_ASHBAL = 2;

    public const QETAA_MOTAKADEM = 3;

    public const QETAA_RAEDAT = 4;

    public const QETAA_JAWWALA = 5;

    public const QETAA_MORSHEDAT = 6;

    public const QETAA_QADA = 7;

    public const QETAA_KASHAFA = 8;

    public const QETAA_ZAHRAT = 9;

    public const QETAA_EADAD_QADA = 10;

    /** @var list<int> */
    public const LEADER_QETAA_IDS = [self::QETAA_QADA, self::QETAA_EADAD_QADA];

    /** @var list<int> */
    public const YOUTH_QETAA_IDS = [1, 2, 3, 4, 6, 8, 9];

    /** @var list<int> */
    public const FROZEN_QETAA_IDS = [5, 7, 10];

    /**
     * Youth ladder sector for sana 3–14. Null outside that range.
     */
    public function resolveYouthSectorId(int $sanaMarhalaId, string $gender): ?int
    {
        if ($sanaMarhalaId >= 3 && $sanaMarhalaId <= 4) {
            return self::QETAA_BARAEM;
        }

        if ($sanaMarhalaId >= 5 && $sanaMarhalaId <= 8) {
            return $gender === 'Male' ? self::QETAA_ASHBAL : self::QETAA_ZAHRAT;
        }

        if ($sanaMarhalaId >= 9 && $sanaMarhalaId <= 11) {
            return $gender === 'Male' ? self::QETAA_KASHAFA : self::QETAA_MORSHEDAT;
        }

        if ($sanaMarhalaId >= 12 && $sanaMarhalaId <= 14) {
            return $gender === 'Male' ? self::QETAA_MOTAKADEM : self::QETAA_RAEDAT;
        }

        return null;
    }

    /**
     * @return list<array{0: int, 1: string, 2: string}> [qetaaId, name, gender]
     */
    public function resolve(int $sanaMarhalaId, string $gender, bool $newLeadersSchool): array
    {
        if ($newLeadersSchool && $sanaMarhalaId > 14) {
            return [
                [self::QETAA_EADAD_QADA, 'اعداد قادة', $gender],
            ];
        }

        $youthId = $this->resolveYouthSectorId($sanaMarhalaId, $gender);
        if ($youthId !== null) {
            return [[$youthId, $this->qetaaName($youthId), $gender]];
        }

        if ($sanaMarhalaId <= 21 && $sanaMarhalaId > 14) {
            return [
                [self::QETAA_JAWWALA, 'جوالة', $gender],
                [self::QETAA_QADA, 'قادة', $gender],
            ];
        }

        return [
            [self::QETAA_QADA, 'قادة', $gender],
        ];
    }

    public function qetaaName(int $qetaaId): string
    {
        return match ($qetaaId) {
            self::QETAA_BARAEM => 'براعم',
            self::QETAA_ASHBAL => 'أشبال',
            self::QETAA_MOTAKADEM => 'متقدم',
            self::QETAA_RAEDAT => 'رائدات',
            self::QETAA_JAWWALA => 'جوالة',
            self::QETAA_MORSHEDAT => 'مرشدات',
            self::QETAA_QADA => 'قادة',
            self::QETAA_KASHAFA => 'كشافة',
            self::QETAA_ZAHRAT => 'زهرات',
            self::QETAA_EADAD_QADA => 'اعداد قادة',
            default => (string) $qetaaId,
        };
    }
}
