<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy Qetaa.QetaaID / Curricula.CurriculaID are signed INT — unsigned FKs fail (MySQL 3780).
        if (! Schema::hasTable('CurriculumPlan')) {
            Schema::create('CurriculumPlan', function (Blueprint $table) {
                $table->increments('PlanID');
                $table->integer('QetaaID');
                $table->string('PlanName');
                $table->integer('SortOrder')->default(0);
                $table->unsignedTinyInteger('IsActive')->default(0);
                $table->timestamps();

                $table->index('QetaaID');
                $table->index(['QetaaID', 'IsActive']);
            });
        } else {
            // Recover from a prior failed deploy that created the table then failed on the FK.
            $this->ensureSignedIntegerColumn('CurriculumPlan', 'QetaaID');
        }

        $this->ensureForeignKey(
            'CurriculumPlan',
            'curriculumplan_qetaaid_foreign',
            'QetaaID',
            'Qetaa',
            'QetaaID'
        );

        if (! Schema::hasTable('CurriculumPlanLecture')) {
            Schema::create('CurriculumPlanLecture', function (Blueprint $table) {
                $table->unsignedInteger('PlanID');
                $table->integer('CurriculaID');
                $table->integer('SortOrder')->default(0);

                $table->primary(['PlanID', 'CurriculaID']);
                $table->index('CurriculaID');

                $table->foreign('PlanID')
                    ->references('PlanID')
                    ->on('CurriculumPlan')
                    ->onDelete('cascade');
            });
        } else {
            $this->ensureSignedIntegerColumn('CurriculumPlanLecture', 'CurriculaID');
        }

        $this->ensureForeignKey(
            'CurriculumPlanLecture',
            'curriculumplanlecture_curriculaid_foreign',
            'CurriculaID',
            'Curricula',
            'CurriculaID',
            'restrict'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('CurriculumPlanLecture');
        Schema::dropIfExists('CurriculumPlan');
    }

    private function ensureSignedIntegerColumn(string $table, string $column): void
    {
        $row = DB::selectOne(
            'SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?',
            [$table, $column]
        );

        if (! $row) {
            return;
        }

        $type = strtolower((string) $row->COLUMN_TYPE);
        if (! str_contains($type, 'unsigned')) {
            return;
        }

        $nullable = strtoupper((string) $row->IS_NULLABLE) === 'YES' ? 'NULL' : 'NOT NULL';
        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` INT {$nullable}");
    }

    private function ensureForeignKey(
        string $table,
        string $constraint,
        string $column,
        string $refTable,
        string $refColumn,
        string $onDelete = 'restrict'
    ): void {
        if (! Schema::hasTable($refTable)) {
            return;
        }

        $exists = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = \'FOREIGN KEY\'',
            [$table, $constraint]
        );

        if ($exists) {
            return;
        }

        $onDeleteSql = strtoupper($onDelete) === 'CASCADE' ? 'ON DELETE CASCADE' : 'ON DELETE RESTRICT';
        DB::statement(
            "ALTER TABLE `{$table}`
             ADD CONSTRAINT `{$constraint}`
             FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) {$onDeleteSql}"
        );
    }
};
