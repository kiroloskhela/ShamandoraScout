<?php

namespace App\Domain\Enrolment;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * National-ID (RaqamQawmy) uniqueness across main + staging enrolment tables.
 */
class LiveFormDuplicateCheck
{
    /** @var list<string> */
    private const TABLES = [
        'PersonInformation',
        'NewUsersInformation',
        'NewUsersInformationWaitinglist',
    ];

    public function exists(string $raqamQawmy, bool $lockForUpdate = false): bool
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table)->where('RaqamQawmy', $raqamQawmy);

            if ($lockForUpdate) {
                $query->lockForUpdate();
            }

            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }

    public function isUniqueViolation(QueryException $e): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $driverCode = (int) ($e->errorInfo[1] ?? 0);
        $message = strtolower($e->getMessage());

        if ($sqlState === '23000' || $driverCode === 1062) {
            return true;
        }

        return str_contains($message, 'unique constraint')
            || str_contains($message, 'duplicate entry');
    }
}
