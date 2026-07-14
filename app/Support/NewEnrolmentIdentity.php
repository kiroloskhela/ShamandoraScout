<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PersonID allocation rules shared by the new-enrolment (liveform) insert
 * paths for `NewUsersInformation` and `NewUsersInformationWaitinglist`.
 *
 * Package A (see database/migrations/*_package_a_new_users_keys.php on
 * fix/db-identity-enrolment / testing) adds a surrogate AUTO_INCREMENT `id`
 * primary key to both tables. Once that migration has run in a given
 * environment, PersonID should simply mirror the freshly minted `id`
 * (obtained via insertGetId) instead of being computed by hand.
 *
 * FLAG: as of this fix, Package A's migrations have not been merged/run on
 * `main` (production may not have them either), so this class keeps the
 * legacy locked MAX(PersonID)+1 behaviour as a fallback for environments
 * that haven't been migrated yet. Once Package A is confirmed live
 * everywhere, the fallback branch (and this dual-path check) can be
 * removed in favour of always using the surrogate id.
 */
class NewEnrolmentIdentity
{
    /**
     * Whether {$table} already has the Package A surrogate AUTO_INCREMENT
     * `id` primary key.
     */
    public static function hasAutoIncrementSurrogateId(string $table): bool
    {
        if (!Schema::hasColumn($table, 'id')) {
            return false;
        }

        if (DB::getDriverName() !== 'mysql') {
            // Non-MySQL connections (e.g. sqlite in tests) don't expose
            // AUTO_INCREMENT via information_schema the same way; column
            // presence is treated as sufficient signal there.
            return true;
        }

        $row = DB::selectOne(
            'SELECT EXTRA FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ? AND column_name = ?',
            [DB::getDatabaseName(), $table, 'id']
        );

        return $row !== null && str_contains(strtolower($row->EXTRA ?? ''), 'auto_increment');
    }

    /**
     * Pure legacy fallback: given the current highest PersonID in the table
     * (or null when it's empty), return the next id to assign. Caller is
     * responsible for holding a row lock (e.g. lockForUpdate()) around the
     * read that produced $currentMaxPersonId.
     */
    public static function nextLegacyPersonId(?int $currentMaxPersonId): int
    {
        return $currentMaxPersonId === null ? 1 : $currentMaxPersonId + 1;
    }
}
