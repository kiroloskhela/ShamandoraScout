<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Allow CSV campaigns: recipients without a PersonID, unique by phone per campaign.
 * MySQL production hardening only — fresh installs already get the correct shape
 * from 2026_07_15_400003.
 *
 * Production still has uq_wa_campaign_person (campaign_id, person_id), which InnoDB
 * also uses to support the campaign_id foreign key. Drop FKs first, then indexes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!$this->isMySql() || !Schema::hasTable('whatsapp_campaign_recipients')) {
            return;
        }

        $db = DB::getDatabaseName();

        // Drop FKs that may depend on uq_wa_campaign_person (campaign_id is leftmost).
        $foreignKeys = DB::select(
            'SELECT DISTINCT CONSTRAINT_NAME AS name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$db, 'whatsapp_campaign_recipients']
        );
        foreach ($foreignKeys as $fk) {
            DB::statement(
                'ALTER TABLE `whatsapp_campaign_recipients` DROP FOREIGN KEY `'.$fk->name.'`'
            );
        }

        if ($this->indexExists($db, 'uq_wa_campaign_person')) {
            DB::statement('ALTER TABLE `whatsapp_campaign_recipients` DROP INDEX `uq_wa_campaign_person`');
        }

        DB::statement('ALTER TABLE `whatsapp_campaign_recipients` MODIFY `person_id` INT UNSIGNED NULL');

        if (!$this->indexExists($db, 'uq_wa_campaign_phone')) {
            DB::statement(
                'ALTER TABLE `whatsapp_campaign_recipients`
                 ADD UNIQUE INDEX `uq_wa_campaign_phone` (`campaign_id`, `phone`)'
            );
        }

        // Restore campaign_id FK (supported by uq_wa_campaign_phone).
        if (!$this->foreignKeyExists($db, 'whatsapp_campaign_recipients_campaign_id_foreign')) {
            DB::statement(
                'ALTER TABLE `whatsapp_campaign_recipients`
                 ADD CONSTRAINT `whatsapp_campaign_recipients_campaign_id_foreign`
                 FOREIGN KEY (`campaign_id`) REFERENCES `whatsapp_campaigns` (`id`)
                 ON DELETE CASCADE'
            );
        }
    }

    public function down(): void
    {
        // Forward-preferred; CSV null person_ids make reverting unsafe.
    }

    private function indexExists(string $db, string $indexName): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$db, 'whatsapp_campaign_recipients', $indexName]
        );

        return $row && (int) $row->cnt > 0;
    }

    private function foreignKeyExists(string $db, string $constraintName): bool
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = ?',
            [$db, 'whatsapp_campaign_recipients', $constraintName, 'FOREIGN KEY']
        );

        return $row && (int) $row->cnt > 0;
    }

    private function isMySql(): bool
    {
        return in_array(
            Schema::getConnection()->getDriverName(),
            ['mysql', 'mariadb'],
            true
        );
    }
};
