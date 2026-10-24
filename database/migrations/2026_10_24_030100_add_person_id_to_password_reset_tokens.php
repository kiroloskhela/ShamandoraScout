<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('password_reset_tokens')) {
            return;
        }

        if (! Schema::hasColumn('password_reset_tokens', 'person_id')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->unsignedInteger('person_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('password_reset_tokens') && Schema::hasColumn('password_reset_tokens', 'person_id')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropColumn('person_id');
            });
        }
    }
};
