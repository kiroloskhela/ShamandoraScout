@extends('layouts.app', ['pageTitle' => __('Enrolment form control')])

@section('content')
@php($locale = app()->getLocale())
<div class="max-w-2xl mx-auto" lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-slate-100">{{ __('Enrolment form control') }}</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-slate-400">
            {{ __('When closed, /liveform shows a closed message instead of the form.') }}
        </p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('liveform-settings.update') }}"
        class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <span class="block text-sm font-semibold text-gray-700 dark:text-slate-200 mb-3">{{ __('Registration status') }}</span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition
                    {{ $isOpen ? 'border-teal-500 bg-teal-50 dark:bg-teal-950/30' : 'border-gray-200 dark:border-slate-700' }}">
                    <input type="radio" name="liveform_open" value="1" class="text-teal-700 focus:ring-teal-600"
                        {{ $isOpen ? 'checked' : '' }}>
                    <span>
                        <span class="block font-bold text-teal-800 dark:text-teal-200">{{ __('Open') }}</span>
                        <span class="text-xs text-gray-500">{{ __('New enrolment requests can be submitted') }}</span>
                    </span>
                </label>
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition
                    {{ !$isOpen ? 'border-rose-400 bg-rose-50 dark:bg-rose-950/30' : 'border-gray-200 dark:border-slate-700' }}">
                    <input type="radio" name="liveform_open" value="0" class="text-rose-600 focus:ring-rose-500"
                        {{ !$isOpen ? 'checked' : '' }}>
                    <span>
                        <span class="block font-bold text-rose-800 dark:text-rose-200">{{ __('Closed') }}</span>
                        <span class="text-xs text-gray-500">{{ __('Show closed page to visitors') }}</span>
                    </span>
                </label>
            </div>
        </div>

        <div>
            <label for="liveform_closed_message" class="block text-sm font-semibold text-gray-700 dark:text-slate-200 mb-2">
                {{ __('Closed message') }}
            </label>
            <textarea id="liveform_closed_message" name="liveform_closed_message" rows="4" required
                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-900 shadow-sm focus:border-teal-600 focus:ring-teal-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">{{ old('liveform_closed_message', $closedMessage) }}</textarea>
            @error('liveform_closed_message')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($updatedAt)
            <p class="text-xs text-gray-400">{{ __('Last updated') }}: {{ $updatedAt }}</p>
        @endif

        <div class="flex items-center justify-between gap-3 pt-2">
            <a href="{{ url('/liveform') }}" target="_blank" rel="noopener"
                class="text-sm font-medium text-teal-700 hover:underline">{{ __('Preview public page') }}</a>
            <button type="submit"
                class="inline-flex items-center rounded-xl bg-teal-700 px-6 py-3 text-sm font-bold text-white shadow hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">{{ __('Save') }}</button>
        </div>
    </form>
</div>
@endsection
