<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hot-path indexes for finance bookings, payments, and calendar joins.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! $this->isMySql()) {
            return;
        }

        if (Schema::hasTable('SeasonEventParticipantFinance')) {
            $this->addIndexIfMissing(
                'SeasonEventParticipantFinance',
                'idx_sepf_event_refunded',
                ['SeasonEventID', 'IsRefunded']
            );
        }

        if (Schema::hasTable('SeasonEventParticipantFinancePayment')) {
            $this->addIndexIfMissing(
                'SeasonEventParticipantFinancePayment',
                'idx_sepfp_booking_type',
                ['SeasonEventParticipantFinanceID', 'PaymentType']
            );
        }

        if (Schema::hasTable('EventQetaa')) {
            $this->addIndexIfMissing('EventQetaa', 'idx_eventqetaa_eventid', ['EventID']);
        }

        if (Schema::hasTable('SeasonEvent')) {
            $this->addIndexIfMissing('SeasonEvent', 'idx_seasonevent_seasonid', ['SeasonID']);
        }

        if (Schema::hasTable('PersonEntryQuestions')) {
            $this->addIndexIfMissing('PersonEntryQuestions', 'idx_peq_personid', ['PersonID']);
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

    /**
     * @param  list<string>  $columns
     */
    private function addIndexIfMissing(string $table, string $name, array $columns): void
    {
        if ($this->indexExists($table, $name)) {
            return;
        }

        $list = implode(', ', array_map(fn ($column) => "`{$column}`", $columns));
        DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$list})");
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
