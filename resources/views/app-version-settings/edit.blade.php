@extends('layouts.app', ['pageTitle' => __('App version settings')])

@section('content')
@php($locale = app()->getLocale())
<div class="max-w-3xl mx-auto" lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-slate-100">{{ __('App version settings') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">
            {{ __('Controls what mobile apps see from GET /api/version/check') }}
        </p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('app-version-settings.update') }}"
        class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900 space-y-8">
        @csrf
        @method('PUT')

        @foreach (['android' => 'Android', 'ios' => 'iPhone / iOS'] as $platform => $label)
            <section class="space-y-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-slate-100">{{ $label }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __('Latest version') }}</label>
                        <input type="text" name="{{ $platform }}_latest_version" required
                            value="{{ old($platform.'_latest_version', $config[$platform]['latest_version']) }}"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">{{ __('Minimum version') }}</label>
                        <input type="text" name="{{ $platform }}_min_version" required
                            value="{{ old($platform.'_min_version', $config[$platform]['min_version']) }}"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold mb-1">{{ __('Store URL') }}</label>
                        <input type="url" name="{{ $platform }}_url" required
                            value="{{ old($platform.'_url', $config[$platform]['url']) }}"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                    </div>
                    <div class="sm:col-span-2">
                        <span class="block text-sm font-semibold mb-2">{{ __('Force update') }}</span>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="{{ $platform }}_force_update" value="1"
                                    {{ old($platform.'_force_update', $config[$platform]['force_update'] ? '1' : '0') === '1' ? 'checked' : '' }}>
                                <span>{{ __('Yes') }}</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="{{ $platform }}_force_update" value="0"
                                    {{ old($platform.'_force_update', $config[$platform]['force_update'] ? '1' : '0') === '0' ? 'checked' : '' }}>
                                <span>{{ __('No') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>
        @endforeach

        <section class="space-y-4 border-t border-gray-100 dark:border-slate-700 pt-6">
            <h2 class="text-lg font-bold">{{ __('Maintenance') }}</h2>
            <div class="flex gap-4 mb-3">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="maintenance_enabled" value="1"
                        {{ old('maintenance_enabled', $config['maintenance']['enabled'] ? '1' : '0') === '1' ? 'checked' : '' }}>
                    <span>{{ __('Enabled') }}</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="maintenance_enabled" value="0"
                        {{ old('maintenance_enabled', $config['maintenance']['enabled'] ? '1' : '0') === '0' ? 'checked' : '' }}>
                    <span>{{ __('Disabled') }}</span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">{{ __('Maintenance message') }}</label>
                <textarea name="maintenance_message" rows="2" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">{{ old('maintenance_message', $config['maintenance']['message']) }}</textarea>
            </div>
        </section>

        <section class="space-y-4 border-t border-gray-100 dark:border-slate-700 pt-6">
            <h2 class="text-lg font-bold">{{ __('Update dialog copy') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">{{ __('Title') }}</label>
                    <input type="text" name="update_ui_title" required
                        value="{{ old('update_ui_title', $config['update_ui']['title']) }}"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">{{ __('Button') }}</label>
                    <input type="text" name="update_ui_button" required
                        value="{{ old('update_ui_button', $config['update_ui']['button']) }}"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold mb-1">{{ __('Message') }}</label>
                    <textarea name="update_ui_message" rows="2" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">{{ old('update_ui_message', $config['update_ui']['message']) }}</textarea>
                </div>
            </div>
        </section>

        @if ($updatedAt)
            <p class="text-xs text-gray-400">{{ __('Last updated') }}: {{ $updatedAt }}</p>
        @endif

        <div class="flex justify-end pt-2">
            <button type="submit"
                class="inline-flex items-center rounded-xl bg-teal-700 px-6 py-3 text-sm font-bold text-white shadow hover:bg-teal-800">
                {{ __('Save') }}
            </button>
        </div>
    </form>
</div>
@endsection
