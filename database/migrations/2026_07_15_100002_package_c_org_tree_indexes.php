<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Package C: org tree / membership indexes.
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

        if (Schema::hasTable('GroupTable')) {
            $this->addIndexIfMissing('GroupTable', 'idx_grouptable_included_under', 'IncludedUnderGroupID');
            $this->addIndexIfMissing('GroupTable', 'idx_grouptable_grouptypeid', 'GroupTypeID');
        }

        if (Schema::hasTable('PersonGroup')) {
            $this->addIndexIfMissing('PersonGroup', 'idx_persongroup_groupid', 'GroupID');
            $this->addIndexIfMissing('PersonGroup', 'idx_persongroup_grouproleid', 'GroupRoleID');
            if (!$this->indexExists('PersonGroup', 'uq_persongroup_person_group_role')) {
                DB::statement('ALTER TABLE `PersonGroup` ADD UNIQUE INDEX `uq_persongroup_person_group_role` (`PersonID`, `GroupID`, `GroupRoleID`)');
            }
        }

        if (Schema::hasTable('GroupQetaa') && !$this->indexExists('GroupQetaa', 'uq_groupqetaa_group_qetaa')) {
            DB::statement('ALTER TABLE `GroupQetaa` ADD UNIQUE INDEX `uq_groupqetaa_group_qetaa` (`GroupID`, `QetaaID`)');
        }

        if (Schema::hasTable('PersonQetaa')) {
            if (!$this->hasPrimaryKey('PersonQetaa')) {
                DB::statement('ALTER TABLE `PersonQetaa` ADD PRIMARY KEY (`PersonID`, `QetaaID`)');
            }
            $this->addIndexIfMissing('PersonQetaa', 'idx_personqetaa_qetaaid', 'QetaaID');
        }

        if (Schema::hasTable('PersonSanaMarhala')) {
            if (!$this->hasPrimaryKey('PersonSanaMarhala')) {
                DB::statement('ALTER TABLE `PersonSanaMarhala` ADD PRIMARY KEY (`PersonID`, `SanaMarhalaID`)');
            }
            $this->addIndexIfMissing('PersonSanaMarhala', 'idx_personsanamarhala_smid', 'SanaMarhalaID');
        }
    }

    public function down(): void
    {
        // Forward-preferred.
    }

    private function isMySql(): bool
    {
        return in_array(
            DB::connection($this->getConnection())->getDriverName(),
            ['mysql', 'mariadb'],
            true
        );
    }

    private function addIndexIfMissing(string $table, string $name, string $column): void
    {
        if (!$this->indexExists($table, $name)) {
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` (`{$column}`)");
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
