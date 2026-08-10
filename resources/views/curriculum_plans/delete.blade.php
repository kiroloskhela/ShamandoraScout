@extends('layouts.app', ['pageTitle' => __('Delete curriculum plan')])

@section('content')
    <div class="mx-auto max-w-lg px-4 py-8">
        <a href="{{ route('curriculum-plan.index') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-emerald-700 dark:text-slate-400 dark:hover:text-emerald-300">
            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            {{ __('Back to curriculum plans') }}
        </a>

        <div
            class="mt-6 rounded-2xl border border-red-200 bg-white p-6 shadow-sm dark:border-red-900/50 dark:bg-slate-900 sm:p-8">
            <div
                class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-300">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-9 0h10" />
                </svg>
            </div>

            <h1 class="text-center text-xl font-bold text-slate-900 dark:text-slate-100">
                {{ __('Delete curriculum plan') }}
            </h1>

            @if ((int) $plan->IsActive === 1)
                <p class="mt-3 text-center text-sm text-red-700 dark:text-red-300">
                    {{ __('Deactivate the plan before deleting it.') }}
                </p>
                <p class="mt-2 text-center text-sm text-slate-500 dark:text-slate-400">
                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $plan->PlanName }}</span>
                    · {{ $qetaa->QetaaName ?? $plan->QetaaID }}
                </p>
                <div class="mt-8 flex justify-center">
                    <a href="{{ route('curriculum-plan.edit', $plan->PlanID) }}"
                        class="inline-flex h-11 items-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white hover:bg-emerald-700">
                        {{ __('Go to plan') }}
                    </a>
                </div>
            @else
                <p class="mt-3 text-center text-sm text-slate-600 dark:text-slate-300">
                    {{ __('Are you sure you want to delete this curriculum plan?') }}
                </p>
                <p class="mt-4 text-center">
                    <span class="block text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $plan->PlanName }}</span>
                    <span class="mt-1 block text-sm text-slate-500">{{ $qetaa->QetaaName ?? $plan->QetaaID }}</span>
                </p>
                <p class="mt-3 text-center text-xs text-slate-500 dark:text-slate-400">
                    {{ __('Lecture links on this plan will be removed. Lectures themselves stay in the bank.') }}
                </p>

                <form method="POST" action="{{ route('curriculum-plan.destroy', $plan->PlanID) }}"
                    class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <a href="{{ route('curriculum-plan.index') }}"
                        class="inline-flex h-11 items-center rounded-xl px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        class="inline-flex h-11 items-center rounded-xl bg-red-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">
                        {{ __('Delete plan') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection
