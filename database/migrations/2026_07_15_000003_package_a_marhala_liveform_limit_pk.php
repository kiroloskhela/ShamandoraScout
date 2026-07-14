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
        if (!$this->isMySql()) {
            // MySQL production schema hardening only (information_schema
            // introspection + MySQL-specific DDL). No-op on other drivers
            // (e.g. sqlite under PHPUnit/RefreshDatabase).
            return;
        }

        if (!Schema::hasTable('MarhalaLiveFormLimit')) {
            return;
        }

        // Normalize NULL Year so it can participate in a composite PK.
        DB::table('MarhalaLiveFormLimit')->whereNull('Year')->update(['Year' => 0]);

        if (!$this->hasPrimaryKey('MarhalaLiveFormLimit')) {
            // Collapse accidental duplicates on the composite key before adding PK.
            $this->collapseDuplicateRows();

            DB::statement('
                ALTER TABLE `MarhalaLiveFormLimit`
                MODIFY `Year` INT NOT NULL DEFAULT 0,
                ADD PRIMARY KEY (`QetaaID`, `SanaMarhalaID`, `Year`)
            ');
        }
    }

    /**
     * Collapse rows that share (QetaaID, SanaMarhalaID, Year) down to one
     * row per key, keeping the highest MaxLimit.
     *
     * The table has no other columns to preserve, so this is lossless
     * beyond picking a single MaxLimit for a duplicate group. A delete+join
     * on "MaxLimit < MaxLimit" (the previous approach) leaves duplicates
     * behind when several rows in a group tie on MaxLimit exactly, which
     * would then make the ADD PRIMARY KEY below fail; grouping in PHP
     * handles ties correctly.
     */
    private function collapseDuplicateRows(): void
    {
        $duplicateGroups = DB::table('MarhalaLiveFormLimit')
            ->select('QetaaID', 'SanaMarhalaID', 'Year')
            ->groupBy('QetaaID', 'SanaMarhalaID', 'Year')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $query = DB::table('MarhalaLiveFormLimit')
                ->where('QetaaID', $group->QetaaID)
                ->where('SanaMarhalaID', $group->SanaMarhalaID)
                ->where('Year', $group->Year);

            $maxLimit = $query->max('MaxLimit');

            $query->delete();

            DB::table('MarhalaLiveFormLimit')->insert([
                'QetaaID' => $group->QetaaID,
                'SanaMarhalaID' => $group->SanaMarhalaID,
                'Year' => $group->Year,
                'MaxLimit' => $maxLimit,
            ]);
        }
    }

    public function down(): void
    {
        if (!$this->isMySql()) {
            return;
        }

        if (!Schema::hasTable('MarhalaLiveFormLimit')) {
            return;
        }

        if ($this->hasPrimaryKey('MarhalaLiveFormLimit')) {
            DB::statement('ALTER TABLE `MarhalaLiveFormLimit` DROP PRIMARY KEY');
        }
    }

    private function isMySql(): bool
    {
        return in_array(
            DB::connection($this->getConnection())->getDriverName(),
            ['mysql', 'mariadb'],
            true
        );
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
