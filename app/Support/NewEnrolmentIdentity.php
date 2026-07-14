<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * NewUsersInformation / NewUsersInformationWaitinglist PersonID allocation.
 *
 * Package A adds a surrogate AUTO_INCREMENT `id` PK. Liveform inserts must
 * use that id (insertGetId) and mirror PersonID to it. There is intentionally
 * no MAX(PersonID)+1 fallback — dual paths drift and hide missing migrations.
 *
 * If this throws, run Package A migrations on the environment first.
 */
class NewEnrolmentIdentity
{
    /**
     * Assert the Package A surrogate AUTO_INCREMENT `id` exists on $table.
     *
     * @throws RuntimeException when the schema has not been hardened yet
     */
    public static function assertSurrogateAutoIncrementId(string $table): void
    {
        if (!Schema::hasColumn($table, 'id')) {
            throw new RuntimeException(
                "Table {$table} is missing Package A surrogate column `id`. "
                . 'Run migrations (2026_07_15_000002_package_a_new_users_keys) before accepting liveform enrolments.'
            );
        }

        if (DB::getDriverName() !== 'mysql' && DB::getDriverName() !== 'mariadb') {
            // sqlite (tests) / others: column presence is enough.
            return;
        }

        $row = DB::selectOne(
            'SELECT EXTRA FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ? AND column_name = ?',
            [DB::getDatabaseName(), $table, 'id']
        );

        if ($row === null || !str_contains(strtolower($row->EXTRA ?? ''), 'auto_increment')) {
            throw new RuntimeException(
                "Table {$table}.id exists but is not AUTO_INCREMENT. "
                . 'Re-run Package A new-users-keys migration before accepting liveform enrolments.'
            );
        }
    }
}
