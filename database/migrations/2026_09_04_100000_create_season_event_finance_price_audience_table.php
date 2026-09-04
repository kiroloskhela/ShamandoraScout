<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who each price interval applies to: event sectors (Qetaa), families, guests.
 *
 * Existing price rows are backfilled with every sector of their event plus
 * FAMILY and GUEST, so pre-existing plans keep meaning "everyone pays this".
 * A price row with no audience rows never matches at booking time.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('SeasonEventFinancePrice')) {
            return;
        }

        if (! Schema::hasTable('SeasonEventFinancePriceAudience')) {
            $this->createTable();
        }

        $this->backfillLegacyRows();
    }

    public function down(): void
    {
        Schema::dropIfExists('SeasonEventFinancePriceAudience');
    }

    private function createTable(): void
    {
        Schema::create('SeasonEventFinancePriceAudience', function (Blueprint $table) {
            $table->increments('SeasonEventFinancePriceAudienceID');
            // Signed int: must match SeasonEventFinancePrice.SeasonEventFinancePriceID (int AUTO_INCREMENT).
            $table->integer('SeasonEventFinancePriceID');
            // QETAA | FAMILY | GUEST
            $table->string('AudienceType', 10);
            // Only set for QETAA rows.
            $table->integer('QetaaID')->nullable();

            $table->unique(['SeasonEventFinancePriceID', 'AudienceType', 'QetaaID'], 'uq_SEFPA_price_audience');
            $table->foreign('SeasonEventFinancePriceID', 'fk_SEFPA_price')
                ->references('SeasonEventFinancePriceID')
                ->on('SeasonEventFinancePrice')
                ->cascadeOnDelete();
        });
    }

    /**
     * Price rows that have no audience yet get "everyone": all event sectors + FAMILY + GUEST.
     */
    private function backfillLegacyRows(): void
    {
        $prices = DB::table('SeasonEventFinancePrice as p')
            ->join('SeasonEvent as se', 'p.SeasonEventID', '=', 'se.SeasonEventID')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('SeasonEventFinancePriceAudience as a')
                    ->whereColumn('a.SeasonEventFinancePriceID', 'p.SeasonEventFinancePriceID');
            })
            ->select('p.SeasonEventFinancePriceID', 'se.EventID')
            ->get();

        if ($prices->isEmpty()) {
            return;
        }

        $sectorsByEvent = DB::table('EventQetaa')
            ->whereIn('EventID', $prices->pluck('EventID')->unique()->all())
            ->distinct()
            ->get(['EventID', 'QetaaID'])
            ->groupBy('EventID');

        $rows = [];
        foreach ($prices as $price) {
            $priceId = (int) $price->SeasonEventFinancePriceID;

            foreach (['FAMILY', 'GUEST'] as $type) {
                $rows[] = ['SeasonEventFinancePriceID' => $priceId, 'AudienceType' => $type, 'QetaaID' => null];
            }

            foreach ($sectorsByEvent->get($price->EventID, collect()) as $sector) {
                $rows[] = ['SeasonEventFinancePriceID' => $priceId, 'AudienceType' => 'QETAA', 'QetaaID' => (int) $sector->QetaaID];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('SeasonEventFinancePriceAudience')->insert($chunk);
        }
    }
};
