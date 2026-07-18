<?php

namespace App\Http\Controllers;

use App\Support\AppSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppVersionSettingsController extends Controller
{
    public function edit()
    {
        $config = AppSettings::appVersionConfig();

        return view('app-version-settings.edit', [
            'config' => $config,
            'updatedAt' => DB::table('AppSettings')
                ->whereIn('SettingKey', [
                    'android_latest_version',
                    'ios_latest_version',
                ])
                ->max('UpdatedAt'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'android_latest_version' => 'required|string|max:40',
            'android_min_version' => 'required|string|max:40',
            'android_force_update' => 'required|in:0,1',
            'android_url' => 'required|url|max:500',
            'ios_latest_version' => 'required|string|max:40',
            'ios_min_version' => 'required|string|max:40',
            'ios_force_update' => 'required|in:0,1',
            'ios_url' => 'required|url|max:500',
            'maintenance_enabled' => 'required|in:0,1',
            'maintenance_message' => 'required|string|max:1000',
            'update_ui_title' => 'required|string|max:120',
            'update_ui_message' => 'required|string|max:500',
            'update_ui_button' => 'required|string|max:80',
        ]);

        foreach ($data as $key => $value) {
            AppSettings::set($key, (string) $value);
        }

        return redirect()
            ->route('app-version-settings.edit')
            ->with('status', 'تم حفظ إعدادات إصدارات التطبيق. الـ API يقرأ القيم الجديدة فوراً.');
    }
}
