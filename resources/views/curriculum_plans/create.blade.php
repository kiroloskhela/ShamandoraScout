@extends('layouts.app', ['pageTitle' => __('Add curriculum plan')])

@section('content')
    <div class="mx-auto max-w-xl px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('curriculum-plan.index') }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-emerald-700 dark:text-slate-400 dark:hover:text-emerald-300">
                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to curriculum plans') }}
            </a>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                {{ __('Add curriculum plan') }}
            </h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                {{ __('Choose a sector and name the syllabus plan. You can attach lectures after creating it.') }}
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200"
                role="alert">
                <ul class="list-disc ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('curriculum-plan.insert') }}"
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-8">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="qetaa_id" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ __('Sector') }}
                    </label>
                    <select id="qetaa_id" name="qetaa_id" required
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="">{{ __('-- Choose sector --') }}</option>
                        @foreach ($qetaat as $qetaa)
                            <option value="{{ $qetaa->QetaaID }}" @selected((string) old('qetaa_id') === (string) $qetaa->QetaaID)>
                                {{ $qetaa->QetaaName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="plan_name" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ __('Plan name') }}
                    </label>
                    <input id="plan_name" type="text" name="plan_name" required value="{{ old('plan_name') }}"
                        placeholder="{{ __('e.g. Year 1') }}"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
                </div>

                <div>
                    <label for="sort_order" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        {{ __('Sort order') }}
                    </label>
                    <input id="sort_order" type="number" name="sort_order" min="0" max="9999"
                        value="{{ old('sort_order', 0) }}"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('Lower numbers appear first within the sector.') }}
                    </p>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-6 dark:border-slate-800">
                <a href="{{ route('curriculum-plan.index') }}"
                    class="inline-flex h-11 items-center rounded-xl px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="inline-flex h-11 items-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                    {{ __('Create plan') }}
                </button>
            </div>
        </form>
    </div>
@endsection
