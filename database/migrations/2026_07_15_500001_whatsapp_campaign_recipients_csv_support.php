<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Allow CSV campaigns: recipients without a PersonID, unique by phone per campaign.
 * MySQL production hardening only — fresh installs already get the correct shape
 * from 2026_07_15_400003.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!$this->isMySql() || !Schema::hasTable('whatsapp_campaign_recipients')) {
            return;
        }

        $db = DB::getDatabaseName();

        $personUq = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$db, 'whatsapp_campaign_recipients', 'uq_wa_campaign_person']
        );
        if ($personUq && (int) $personUq->cnt > 0) {
            DB::statement('ALTER TABLE `whatsapp_campaign_recipients` DROP INDEX `uq_wa_campaign_person`');
        }

        // Make person_id nullable (idempotent enough for INT UNSIGNED NULL)
        DB::statement('ALTER TABLE `whatsapp_campaign_recipients` MODIFY `person_id` INT UNSIGNED NULL');

        $phoneUq = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$db, 'whatsapp_campaign_recipients', 'uq_wa_campaign_phone']
        );
        if (!$phoneUq || (int) $phoneUq->cnt === 0) {
            DB::statement('ALTER TABLE `whatsapp_campaign_recipients` ADD UNIQUE INDEX `uq_wa_campaign_phone` (`campaign_id`, `phone`)');
        }
    }

    public function down(): void
    {
        // Forward-preferred; CSV null person_ids make reverting unsafe.
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
