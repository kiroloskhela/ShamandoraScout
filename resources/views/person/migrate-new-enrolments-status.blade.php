@extends('layouts.app', ['pageTitle' => __('Migration completed')])

@section('content')
    <div class="max-w-xl mx-auto px-4 py-10">
        <div
            class="rounded-2xl border border-emerald-200 dark:border-emerald-900 bg-white dark:bg-slate-900 shadow-sm p-8 text-center space-y-5">
            <div
                class="mx-auto w-14 h-14 rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 flex items-center justify-center text-2xl font-black">
                ✓
            </div>

            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">
                {{ __('Migration completed') }}
            </h2>

            <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                {{ __('Approved new enrolments were transferred to the main system successfully.') }}
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                <a href="{{ route('person.new-enrolments-migrate-index') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-3 transition-colors duration-200">
                    {{ __('Back to migration buttons') }}
                </a>
                <a href="{{ route('person.index') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 font-bold px-5 py-3 transition-colors duration-200">
                    {{ __('Go to system users') }}
                </a>
            </div>
        </div>
    </div>
@endsection
