<?php

namespace App\Domain\Enrolment;

/**
 * Maps sana marhala + gender (+ leaders school) to scout sectors for liveform step 1.
 */
class LiveFormQetaaResolver
{
    /**
     * @return list<array{0: int, 1: string, 2: string}> [qetaaId, name, gender]
     */
    public function resolve(int $sanaMarhalaId, string $gender, bool $newLeadersSchool): array
    {
        if ($newLeadersSchool && $sanaMarhalaId > 14) {
            return [
                [10, 'اعداد قادة', $gender],
            ];
        }

        if ($sanaMarhalaId < 5 && $sanaMarhalaId > 2) {
            return [
                [1, 'براعم', $gender],
            ];
        }

        if ($sanaMarhalaId < 9 && $sanaMarhalaId > 4) {
            return [
                [$gender === 'Male' ? 2 : 9, $gender === 'Male' ? 'أشبال' : 'زهرات', $gender],
            ];
        }

        if ($sanaMarhalaId < 12 && $sanaMarhalaId > 8) {
            return [
                [$gender === 'Male' ? 8 : 6, $gender === 'Male' ? 'كشافة' : 'مرشدات', $gender],
            ];
        }

        if ($sanaMarhalaId <= 14 && $sanaMarhalaId > 11) {
            return [
                [$gender === 'Male' ? 3 : 4, $gender === 'Male' ? 'متقدم' : 'رائدات', $gender],
            ];
        }

        if ($sanaMarhalaId <= 21 && $sanaMarhalaId > 14) {
            return [
                [5, 'جوالة', $gender],
                [7, 'قادة', $gender],
            ];
        }

        return [
            [7, 'قادة', $gender],
        ];
    }
}
