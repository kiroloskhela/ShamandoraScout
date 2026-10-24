<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('AppSettings')) {
            return;
        }

        $exists = DB::table('AppSettings')->where('SettingKey', 'whatsapp_enabled')->exists();
        if ($exists) {
            return;
        }

        DB::table('AppSettings')->insert([
            'SettingKey' => 'whatsapp_enabled',
            'SettingValue' => '0',
            'UpdatedAt' => now(),
            'UpdatedByPersonID' => null,
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('AppSettings')) {
            return;
        }

        DB::table('AppSettings')->where('SettingKey', 'whatsapp_enabled')->delete();
    }
};
