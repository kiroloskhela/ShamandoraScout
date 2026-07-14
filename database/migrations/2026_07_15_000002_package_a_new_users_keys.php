<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Package A: harden NewUsersInformation (+ waiting list) for safe locks/lookups.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!$this->isMySql()) {
            // MySQL production schema hardening only (information_schema
            // introspection + MySQL-specific DDL). No-op on other drivers
            // (e.g. sqlite under PHPUnit/RefreshDatabase).
            return;
        }

        $this->harden('NewUsersInformation', 'nui');
        $this->harden('NewUsersInformationWaitinglist', 'nuiwl');
    }

    public function down(): void
    {
        // Forward-only: removing surrogate PK / uniques on live enrolment tables is unsafe.
    }

    private function isMySql(): bool
    {
        return in_array(
            DB::connection($this->getConnection())->getDriverName(),
            ['mysql', 'mariadb'],
            true
        );
    }

    private function harden(string $table, string $prefix): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $this->assertNoDuplicates($table, 'RaqamQawmy');

        if (!$this->hasPrimaryKey($table)) {
            DB::statement("ALTER TABLE `{$table}` ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        }

        $uq = "uq_{$prefix}_raqam_qawmy";
        if (!$this->indexExists($table, $uq)) {
            DB::statement("ALTER TABLE `{$table}` ADD UNIQUE INDEX `{$uq}` (`RaqamQawmy`)");
        }

        $idx = "idx_{$prefix}_qetaa_sana";
        if (!$this->indexExists($table, $idx)) {
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$idx}` (`QetaaID`, `SanaMarhalaID`)");
        }
    }

    private function assertNoDuplicates(string $table, string $column): void
    {
        $dupes = DB::table($table)
            ->select($column, DB::raw('COUNT(*) as cnt'))
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->having('cnt', '>', 1)
            ->count();

        if ($dupes > 0) {
            throw new \RuntimeException(
                "Cannot add UNIQUE on {$table}.{$column}: {$dupes} duplicate value(s) found. ".
                'Run `php artisan scout:audit-identity-duplicates` first.'
            );
        }
    }

    private function hasPrimaryKey(string $table): bool
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM information_schema.table_constraints
             WHERE table_schema = ? AND table_name = ? AND constraint_type = ?',
            [$database, $table, 'PRIMARY KEY']
        );

        return $row && (int) $row->cnt > 0;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return $row && (int) $row->cnt > 0;
    }
};
