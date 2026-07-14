<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Package B: Roles PK + PersonRole integrity.
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

        if (Schema::hasTable('Roles') && !$this->hasPrimaryKey('Roles')) {
            // Ensure RoleID values are unique before PK.
            $dupes = DB::table('Roles')
                ->select('RoleID', DB::raw('COUNT(*) as cnt'))
                ->groupBy('RoleID')
                ->having('cnt', '>', 1)
                ->count();
            if ($dupes > 0) {
                throw new \RuntimeException('Duplicate RoleID values exist; resolve before adding PK on Roles.');
            }
            DB::statement('ALTER TABLE `Roles` ADD PRIMARY KEY (`RoleID`)');
        }

        if (Schema::hasTable('PersonRole')) {
            if (!$this->hasPrimaryKey('PersonRole') && Schema::hasColumn('PersonRole', 'PersonRoleID')) {
                // PersonRoleID exists but was never declared PK in schema dump.
                DB::statement('ALTER TABLE `PersonRole` ADD PRIMARY KEY (`PersonRoleID`)');
            }

            if (!$this->indexExists('PersonRole', 'uq_personrole_person_role')) {
                DB::statement('ALTER TABLE `PersonRole` ADD UNIQUE INDEX `uq_personrole_person_role` (`PersonID`, `RoleID`)');
            }

            if (!$this->indexExists('PersonRole', 'idx_personrole_roleid')) {
                DB::statement('ALTER TABLE `PersonRole` ADD INDEX `idx_personrole_roleid` (`RoleID`)');
            }
        }
    }

    public function down(): void
    {
        // Forward-preferred; dropping auth PKs is unsafe in shared environments.
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

    private function isMySql(): bool
    {
        return in_array(
            DB::connection($this->getConnection())->getDriverName(),
            ['mysql', 'mariadb'],
            true
        );
    }
};
