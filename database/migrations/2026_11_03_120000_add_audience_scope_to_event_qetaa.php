<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * all = everyone in that sector.
 * serving = only people of قادة / جوالة / اعداد قادة who are linked to a group
 * in one of the event's other sectors.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('EventQetaa') || Schema::hasColumn('EventQetaa', 'AudienceScope')) {
            return;
        }

        Schema::table('EventQetaa', function (Blueprint $table) {
            $table->string('AudienceScope', 16)->default('all');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('EventQetaa') || ! Schema::hasColumn('EventQetaa', 'AudienceScope')) {
            return;
        }

        Schema::table('EventQetaa', function (Blueprint $table) {
            $table->dropColumn('AudienceScope');
        });
    }
};
