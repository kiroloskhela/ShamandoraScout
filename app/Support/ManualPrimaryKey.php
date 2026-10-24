<?php

namespace App\Support;

use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RuntimeException;

/**
 * Allocate the next integer PK for legacy tables that are not AUTO_INCREMENT.
 *
 * Pass $write so the insert runs inside the same lock and transaction. The
 * lock is held until that write commits, so two concurrent callers cannot
 * receive the same id. Callers that only read the next id (no $write) still
 * race with a later insert outside this method.
 */
class ManualPrimaryKey
{
    private static bool $rollbackHooked = false;

    /** @var array<int, callable(): void> */
    private static array $lockReleasesOnRollback = [];

    /**
     * @param  null|callable(int): void  $write
     */
    public static function next(string $table, string $column, ?callable $write = null): int
    {
        if (DB::getDriverName() === 'mysql') {
            return self::nextWithMysqlAdvisoryLock($table, $column, $write);
        }

        $allocate = function () use ($table, $column, $write) {
            $id = self::allocate($table, $column, true);
            if ($write !== null) {
                $write($id);
            }

            return $id;
        };

        if (DB::transactionLevel() > 0) {
            return $allocate();
        }

        return DB::transaction($allocate);
    }

    private static function nextWithMysqlAdvisoryLock(string $table, string $column, ?callable $write): int
    {
        $lockName = 'mpk:'.$table.':'.$column;
        $acquired = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockName]);

        if ((int) ($acquired->acquired ?? 0) !== 1) {
            throw new RuntimeException("Could not acquire primary key lock for {$table}.{$column}");
        }

        $released = false;
        $release = function () use ($lockName, &$released): void {
            if ($released) {
                return;
            }
            $released = true;
            DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
        };

        $deferred = false;

        try {
            $allocate = function () use ($table, $column, $write) {
                $id = self::allocate($table, $column, false);
                if ($write !== null) {
                    $write($id);
                }

                return $id;
            };

            if (DB::transactionLevel() > 0) {
                $id = $allocate();
                self::releaseLockWhenTransactionEnds($release);
                $deferred = true;

                return $id;
            }

            return DB::transaction($allocate);
        } catch (\Throwable $e) {
            $release();
            throw $e;
        } finally {
            if (! $deferred && DB::transactionLevel() === 0) {
                $release();
            }
        }
    }

    private static function allocate(string $table, string $column, bool $lockRow): int
    {
        $query = DB::table($table)->orderByDesc($column);
        if ($lockRow) {
            $query->lockForUpdate();
        }

        $last = $query->value($column);

        return $last ? ((int) $last + 1) : 1;
    }

    /**
     * Keep a MySQL advisory lock until the surrounding transaction commits or rolls back.
     */
    private static function releaseLockWhenTransactionEnds(callable $release): void
    {
        $id = count(self::$lockReleasesOnRollback);
        self::$lockReleasesOnRollback[$id] = $release;

        DB::afterCommit(function () use ($id): void {
            if (! isset(self::$lockReleasesOnRollback[$id])) {
                return;
            }
            $release = self::$lockReleasesOnRollback[$id];
            unset(self::$lockReleasesOnRollback[$id]);
            $release();
        });

        if (self::$rollbackHooked) {
            return;
        }

        self::$rollbackHooked = true;
        Event::listen(TransactionRolledBack::class, static function (): void {
            $releases = self::$lockReleasesOnRollback;
            self::$lockReleasesOnRollback = [];
            foreach ($releases as $release) {
                $release();
            }
        });
    }
}
