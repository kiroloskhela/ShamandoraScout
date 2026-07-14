<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Package A: UNIQUE on PersonInformation identity columns.
 *
 * FLAG: Run `php artisan scout:audit-identity-duplicates` first.
 * This migration aborts if duplicates still exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->assertNoDuplicates('PersonInformation', 'RaqamQawmy');
        $this->assertNoDuplicates('PersonInformation', 'ShamandoraCode');

        if (!$this->indexExists('PersonInformation', 'uq_personinformation_raqam_qawmy')) {
            Schema::table('PersonInformation', function ($table) {
                $table->unique('RaqamQawmy', 'uq_personinformation_raqam_qawmy');
            });
        }

        if (!$this->indexExists('PersonInformation', 'uq_personinformation_shamandora_code')) {
            Schema::table('PersonInformation', function ($table) {
                $table->unique('ShamandoraCode', 'uq_personinformation_shamandora_code');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('PersonInformation', 'uq_personinformation_raqam_qawmy')) {
            Schema::table('PersonInformation', function ($table) {
                $table->dropUnique('uq_personinformation_raqam_qawmy');
            });
        }

        if ($this->indexExists('PersonInformation', 'uq_personinformation_shamandora_code')) {
            Schema::table('PersonInformation', function ($table) {
                $table->dropUnique('uq_personinformation_shamandora_code');
            });
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
                'Run `php artisan scout:audit-identity-duplicates` and resolve them first.'
            );
        }
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
