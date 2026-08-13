<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('refresh_tokens') || Schema::hasColumn('refresh_tokens', 'family_id')) {
            return;
        }

        Schema::table('refresh_tokens', function (Blueprint $table) {
            $table->uuid('family_id')->nullable()->after('token_hash');
            $table->index(['user_id', 'family_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('refresh_tokens') || ! Schema::hasColumn('refresh_tokens', 'family_id')) {
            return;
        }

        Schema::table('refresh_tokens', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'family_id']);
            $table->dropColumn('family_id');
        });
    }
};
