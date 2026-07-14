<?php

namespace App\Domain\Enrolment;

use Illuminate\Support\Facades\DB;

/**
 * Atomic liveform capacity checks against MarhalaLiveFormLimit.
 *
 * Callers must already be inside a DB transaction. This locks the limit row
 * (FOR UPDATE) so concurrent enrolments serialize before counting seats.
 */
class LiveFormCapacityService
{
    /**
     * True when the applicant should go to the waiting list:
     * - no limit row / MaxLimit == 0, or
     * - current NewUsersInformation count >= MaxLimit.
     */
    public function shouldUseWaitingList(int $qetaaId, int $sanaMarhalaId): bool
    {
        $limitRow = DB::table('MarhalaLiveFormLimit')
            ->where('QetaaID', $qetaaId)
            ->where('SanaMarhalaID', $sanaMarhalaId)
            ->lockForUpdate()
            ->first();

        $maxLimit = $limitRow ? (int) $limitRow->MaxLimit : 0;

        if ($maxLimit <= 0) {
            return true;
        }

        $currentCount = (int) DB::table('NewUsersInformation')
            ->where('QetaaID', $qetaaId)
            ->where('SanaMarhalaID', $sanaMarhalaId)
            ->count();

        return $currentCount >= $maxLimit;
    }
}
