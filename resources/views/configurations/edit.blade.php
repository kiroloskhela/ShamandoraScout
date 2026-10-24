@extends('layouts.app', ['pageTitle' => __('Configurations')])

@section('content')
@php($locale = app()->getLocale())
<div class="max-w-3xl mx-auto" lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-slate-100">{{ __('Configurations') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">
            {{ __('Turn services on or off without taking the rest of the system down.') }}
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('configurations.update') }}"
        class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900 space-y-4">
        @csrf
        @method('PUT')

        <h2 class="text-lg font-bold text-gray-900 dark:text-slate-100">{{ __('WhatsApp service') }}</h2>
        <p class="text-sm text-gray-500 dark:text-slate-400">
            {{ __('Turn WhatsApp on or off. While it is off, password reset, campaigns, QR codes, and other sends skip WhatsApp and do not fail.') }}
        </p>

        <div class="flex gap-4">
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="whatsapp_enabled" value="1" @checked(old('whatsapp_enabled', $whatsappEnabled ? '1' : '0') === '1')>
                <span>{{ __('On') }}</span>
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="whatsapp_enabled" value="0" @checked(old('whatsapp_enabled', $whatsappEnabled ? '1' : '0') === '0')>
                <span>{{ __('Off') }}</span>
            </label>
        </div>

        <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
            {{ __('Save') }}
        </button>
    </form>
</div>
@endsection
