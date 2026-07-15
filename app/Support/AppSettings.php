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
}
