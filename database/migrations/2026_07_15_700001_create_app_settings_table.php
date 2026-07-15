<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AppSettings', function (Blueprint $table) {
            $table->string('SettingKey', 100)->primary();
            $table->text('SettingValue')->nullable();
            $table->timestamp('UpdatedAt')->nullable();
            $table->unsignedBigInteger('UpdatedByPersonID')->nullable();
        });

        DB::table('AppSettings')->insert([
            [
                'SettingKey' => 'liveform_open',
                'SettingValue' => '1',
                'UpdatedAt' => now(),
                'UpdatedByPersonID' => null,
            ],
            [
                'SettingKey' => 'liveform_closed_message',
                'SettingValue' => 'التسجيل مغلق حالياً. تابعونا لمعرفة موعد فتح باب الالتحاق الجديد.',
                'UpdatedAt' => now(),
                'UpdatedByPersonID' => null,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('AppSettings');
    }
};
