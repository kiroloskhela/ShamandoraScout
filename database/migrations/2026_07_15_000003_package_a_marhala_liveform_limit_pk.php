<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Package A: primary key for MarhalaLiveFormLimit capacity rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('MarhalaLiveFormLimit')) {
            return;
        }

        // Normalize NULL Year so it can participate in a composite PK.
        DB::table('MarhalaLiveFormLimit')->whereNull('Year')->update(['Year' => 0]);

        if (!$this->hasPrimaryKey('MarhalaLiveFormLimit')) {
            // Collapse accidental duplicates on the composite key before adding PK.
            DB::statement('
                DELETE t1 FROM MarhalaLiveFormLimit t1
                INNER JOIN MarhalaLiveFormLimit t2
                WHERE t1.QetaaID = t2.QetaaID
                  AND t1.SanaMarhalaID = t2.SanaMarhalaID
                  AND IFNULL(t1.Year, 0) = IFNULL(t2.Year, 0)
                  AND t1.MaxLimit < t2.MaxLimit
            ');

            DB::statement('
                ALTER TABLE `MarhalaLiveFormLimit`
                MODIFY `Year` INT NOT NULL DEFAULT 0,
                ADD PRIMARY KEY (`QetaaID`, `SanaMarhalaID`, `Year`)
            ');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('MarhalaLiveFormLimit')) {
            return;
        }

        if ($this->hasPrimaryKey('MarhalaLiveFormLimit')) {
            DB::statement('ALTER TABLE `MarhalaLiveFormLimit` DROP PRIMARY KEY');
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
};
