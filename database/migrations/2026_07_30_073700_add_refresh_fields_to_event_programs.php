<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_programs', function (Blueprint $table) {
            $table->text('source_url')->nullable()->after('outro_template');
            $table->timestamp('last_refreshed_at')->nullable()->after('source_url');
            $table->json('known_people_json')->nullable()->after('last_refreshed_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_programs', function (Blueprint $table) {
            $table->dropColumn(['source_url', 'last_refreshed_at', 'known_people_json']);
        });
    }
};
