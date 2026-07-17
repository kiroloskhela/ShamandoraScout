<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Allocate the next integer PK for legacy tables that are not AUTO_INCREMENT.
 * Prefer real AUTO_INCREMENT when available; this exists for production-safe inserts.
 *
 * Concurrent callers are serialized (MySQL GET_LOCK; otherwise a transaction + row lock).
 */
class ManualPrimaryKey
{
    public static function next(string $table, string $column): int
    {
        return DB::transaction(function () use ($table, $column) {
            if (DB::getDriverName() === 'mysql') {
                return self::nextWithMysqlAdvisoryLock($table, $column);
            }

            $last = DB::table($table)
                ->orderByDesc($column)
                ->lockForUpdate()
                ->value($column);

            return $last ? ((int) $last + 1) : 1;
        });
    }

    private static function nextWithMysqlAdvisoryLock(string $table, string $column): int
    {
        $lockName = 'mpk:'.$table.':'.$column;
        $acquired = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockName]);

        if ((int) ($acquired->acquired ?? 0) !== 1) {
            throw new RuntimeException("Could not acquire primary key lock for {$table}.{$column}");
        }

        try {
            $last = DB::table($table)->orderByDesc($column)->value($column);

            return $last ? ((int) $last + 1) : 1;
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
        }
    }
}
