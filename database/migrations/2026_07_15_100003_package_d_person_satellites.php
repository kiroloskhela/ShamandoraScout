<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Package D: person 1:1 satellite PKs + PersonImages FK.
 */
return new class extends Migration
{
    public function up(): void
    {
        $oneToOne = [
            'PersonalPhysicalAddress',
            'PersonJob',
            'PersonLearningInformation',
            'PersonSpiritualFatherInformation',
        ];

        foreach ($oneToOne as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'PersonID')) {
                continue;
            }
            if ($this->hasPrimaryKey($table)) {
                continue;
            }
            $this->assertUniquePersonId($table);
            DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`PersonID`)");
        }

        // PersonPhoneNumbers / PersonSystemPassword / PersonImages already have PK(PersonID)
        // in schema.sql — only add missing FK for images.
        if (Schema::hasTable('PersonImages') && !$this->fkExists('PersonImages', 'fk_personimages_person')) {
            DB::statement('
                ALTER TABLE `PersonImages`
                ADD CONSTRAINT `fk_personimages_person`
                FOREIGN KEY (`PersonID`) REFERENCES `PersonInformation` (`PersonID`)
                ON DELETE CASCADE ON UPDATE CASCADE
            ');
        }

        if (Schema::hasTable('BloodType') && !$this->hasPrimaryKey('BloodType')) {
            $this->assertUniqueColumn('BloodType', 'BloodTypeID');
            DB::statement('ALTER TABLE `BloodType` ADD PRIMARY KEY (`BloodTypeID`)');
        }
    }

    public function down(): void
    {
        // Forward-preferred.
    }

    private function assertUniquePersonId(string $table): void
    {
        $remaining = DB::table($table)
            ->select('PersonID', DB::raw('COUNT(*) as cnt'))
            ->groupBy('PersonID')
            ->having('cnt', '>', 1)
            ->count();
        if ($remaining > 0) {
            throw new \RuntimeException(
                "Cannot add PK(PersonID) on {$table}: {$remaining} PersonID value(s) duplicated. Resolve manually."
            );
        }
    }

    private function assertUniqueColumn(string $table, string $column): void
    {
        $remaining = DB::table($table)
            ->select($column, DB::raw('COUNT(*) as cnt'))
            ->groupBy($column)
            ->having('cnt', '>', 1)
            ->count();
        if ($remaining > 0) {
            throw new \RuntimeException("Cannot add PK on {$table}.{$column}: duplicates exist.");
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

    private function fkExists(string $table, string $name): bool
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM information_schema.table_constraints
             WHERE table_schema = ? AND table_name = ? AND constraint_name = ? AND constraint_type = ?',
            [$database, $table, $name, 'FOREIGN KEY']
        );
        return $row && (int) $row->cnt > 0;
    }
};
