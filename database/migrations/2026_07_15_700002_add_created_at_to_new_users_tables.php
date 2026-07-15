<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['NewUsersInformation', 'NewUsersInformationWaitinglist'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            if (! Schema::hasColumn($table, 'CreatedAt')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->timestamp('CreatedAt')->nullable()->after('EmergencyDetails');
                    $blueprint->index('CreatedAt');
                });
            }

            if (Schema::hasColumn($table, 'PasswordCreationTimestamp')) {
                DB::table($table)
                    ->whereNull('CreatedAt')
                    ->whereNotNull('PasswordCreationTimestamp')
                    ->update([
                        'CreatedAt' => DB::raw('PasswordCreationTimestamp'),
                    ]);
            }
        }
    }

    public function down(): void
    {
        foreach (['NewUsersInformation', 'NewUsersInformationWaitinglist'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'CreatedAt')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropIndex(['CreatedAt']);
                    $blueprint->dropColumn('CreatedAt');
                });
            }
        }
    }
};
