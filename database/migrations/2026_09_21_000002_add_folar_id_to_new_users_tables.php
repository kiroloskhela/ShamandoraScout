<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['NewUsersInformation', 'NewUsersInformationWaitinglist'] as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'FolarID')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $column = $blueprint->unsignedInteger('FolarID')->nullable();
                if (Schema::hasColumn($table, 'EmergencyDetails')) {
                    $column->after('EmergencyDetails');
                }
                $blueprint->index('FolarID');
            });
        }
    }

    public function down(): void
    {
        foreach (['NewUsersInformation', 'NewUsersInformationWaitinglist'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'FolarID')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['FolarID']);
                $blueprint->dropColumn('FolarID');
            });
        }
    }
};
