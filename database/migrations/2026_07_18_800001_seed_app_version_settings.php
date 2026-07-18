<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'android_latest_version' => '1.0.8',
            'android_min_version' => '1.0.8',
            'android_force_update' => '0',
            'android_url' => 'https://play.google.com/store/apps/details?id=com.shamandora.shamandora',
            'ios_latest_version' => '1.0.8',
            'ios_min_version' => '1.0.8',
            'ios_force_update' => '0',
            'ios_url' => 'https://apps.apple.com/us/app/shamandora/id6760709448',
            'maintenance_enabled' => '0',
            'maintenance_message' => 'Server under maintenance',
            'update_ui_title' => 'Update Required',
            'update_ui_message' => 'Please update the app',
            'update_ui_button' => 'Update',
        ];

        $now = now();
        foreach ($defaults as $key => $value) {
            $exists = DB::table('AppSettings')->where('SettingKey', $key)->exists();
            if ($exists) {
                continue;
            }

            DB::table('AppSettings')->insert([
                'SettingKey' => $key,
                'SettingValue' => $value,
                'UpdatedAt' => $now,
                'UpdatedByPersonID' => null,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('AppSettings')->whereIn('SettingKey', [
            'android_latest_version',
            'android_min_version',
            'android_force_update',
            'android_url',
            'ios_latest_version',
            'ios_min_version',
            'ios_force_update',
            'ios_url',
            'maintenance_enabled',
            'maintenance_message',
            'update_ui_title',
            'update_ui_message',
            'update_ui_button',
        ])->delete();
    }
};
