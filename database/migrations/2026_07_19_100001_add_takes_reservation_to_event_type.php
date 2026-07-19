<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('EventType', 'TakesReservation')) {
            Schema::table('EventType', function (Blueprint $table) {
                $table->boolean('TakesReservation')->default(false)->after('EventTypeName');
            });
        }

        DB::table('EventType')
            ->whereIn('EventTypeName', ['معسكر مجمع', 'يوم مجمع'])
            ->update(['TakesReservation' => 1]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('EventType', 'TakesReservation')) {
            Schema::table('EventType', function (Blueprint $table) {
                $table->dropColumn('TakesReservation');
            });
        }
    }
};
