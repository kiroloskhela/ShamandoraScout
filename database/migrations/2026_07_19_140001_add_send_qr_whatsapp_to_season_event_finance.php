<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('SeasonEventFinance', 'SendQrWhatsApp')) {
            Schema::table('SeasonEventFinance', function (Blueprint $table) {
                $table->unsignedTinyInteger('SendQrWhatsApp')->default(0)->after('HaveShirt');
            });
        }

        // Enable QR WhatsApp for existing finance plans on reservation event types.
        $seasonEventIds = DB::table('SeasonEventFinance as sef')
            ->join('SeasonEvent as se', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->join('EventType as et', 'et.EventTypeID', '=', 'e.EventTypeID')
            ->where('et.TakesReservation', 1)
            ->pluck('sef.SeasonEventID');

        if ($seasonEventIds->isNotEmpty()) {
            DB::table('SeasonEventFinance')
                ->whereIn('SeasonEventID', $seasonEventIds)
                ->update(['SendQrWhatsApp' => 1]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('SeasonEventFinance', 'SendQrWhatsApp')) {
            Schema::table('SeasonEventFinance', function (Blueprint $table) {
                $table->dropColumn('SendQrWhatsApp');
            });
        }
    }
};
