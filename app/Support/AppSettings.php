<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AppSettings
{
    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            if (! Schema::hasTable('AppSettings')) {
                return $default;
            }

            $value = Cache::remember("app_setting.{$key}", 60, function () use ($key) {
                $row = DB::table('AppSettings')->where('SettingKey', $key)->first();

                return $row?->SettingValue;
            });

            return $value ?? $default;
        } catch (Throwable) {
            return $default;
        }
    }

    public static function set(string $key, ?string $value): void
    {
        DB::table('AppSettings')->updateOrInsert(
            ['SettingKey' => $key],
            [
                'SettingValue' => $value,
                'UpdatedAt' => now(),
                'UpdatedByPersonID' => Auth::id(),
            ]
        );

        Cache::forget("app_setting.{$key}");
    }

    public static function liveformIsOpen(): bool
    {
        return self::get('liveform_open', '1') === '1';
    }

    public static function liveformClosedMessage(): string
    {
        return (string) self::get(
            'liveform_closed_message',
            'التسجيل مغلق حالياً. تابعونا لمعرفة موعد فتح باب الالتحاق الجديد.'
        );
    }

    /**
     * Mobile version payload for GET /api/version/check (DB overrides config/app_version.php).
     *
     * @return array{
     *   android: array{latest_version: string, min_version: string, force_update: bool, url: string},
     *   ios: array{latest_version: string, min_version: string, force_update: bool, url: string},
     *   maintenance: array{enabled: bool, message: string},
     *   update_ui: array{title: string, message: string, button: string}
     * }
     */
    public static function appVersionConfig(): array
    {
        $file = config('app_version', []);

        $android = $file['android'] ?? [];
        $ios = $file['ios'] ?? [];
        $maintenance = $file['maintenance'] ?? [];
        $updateUi = $file['update_ui'] ?? [];

        return [
            'android' => [
                'latest_version' => (string) self::get('android_latest_version', $android['latest_version'] ?? '1.0.0'),
                'min_version' => (string) self::get('android_min_version', $android['min_version'] ?? '1.0.0'),
                'force_update' => self::get('android_force_update', ($android['force_update'] ?? false) ? '1' : '0') === '1',
                'url' => (string) self::get('android_url', $android['url'] ?? ''),
            ],
            'ios' => [
                'latest_version' => (string) self::get('ios_latest_version', $ios['latest_version'] ?? '1.0.0'),
                'min_version' => (string) self::get('ios_min_version', $ios['min_version'] ?? '1.0.0'),
                'force_update' => self::get('ios_force_update', ($ios['force_update'] ?? false) ? '1' : '0') === '1',
                'url' => (string) self::get('ios_url', $ios['url'] ?? ''),
            ],
            'maintenance' => [
                'enabled' => self::get('maintenance_enabled', ($maintenance['enabled'] ?? false) ? '1' : '0') === '1',
                'message' => (string) self::get('maintenance_message', $maintenance['message'] ?? 'Server under maintenance'),
            ],
            'update_ui' => [
                'title' => (string) self::get('update_ui_title', $updateUi['title'] ?? 'Update Required'),
                'message' => (string) self::get('update_ui_message', $updateUi['message'] ?? 'Please update the app'),
                'button' => (string) self::get('update_ui_button', $updateUi['button'] ?? 'Update'),
            ],
        ];
    }
}
