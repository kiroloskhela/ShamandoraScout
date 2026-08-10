@extends('layouts.app', ['pageTitle' => __('Curriculum plans')])

@section('content')
    @php
        $grouped = $plans->groupBy('QetaaID');
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-8">
        {{-- Page header --}}
        <div
            class="relative mb-8 overflow-hidden rounded-2xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50 via-white to-teal-50 px-6 py-7 shadow-sm dark:border-emerald-900/40 dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-900">
            <div class="pointer-events-none absolute -end-10 -top-10 h-40 w-40 rounded-full bg-emerald-200/40 blur-3xl dark:bg-emerald-700/20">
            </div>
            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">
                        {{ __('Manhaj') }}
                    </p>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                        {{ __('Curriculum plans') }}
                    </h1>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        {{ __('Build syllabus plans per sector, pick lectures from the bank, and activate one live plan for each sector.') }}
                    </p>
                </div>
                <a href="{{ route('curriculum-plan.create') }}"
                    class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Add curriculum plan') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div
                class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200"
                role="status">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div
                class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200"
                role="alert">
                {{ session('error') }}
            </div>
        @endif

        {{-- Sector filters --}}
        <div class="mb-6 flex flex-wrap gap-2" role="navigation" aria-label="{{ __('Sector') }}">
            <a href="{{ route('curriculum-plan.index') }}"
                class="rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ $filterQetaaId === null ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800' }}">
                {{ __('All sectors') }}
            </a>
            @foreach ($qetaat as $qetaa)
                <a href="{{ route('curriculum-plan.index', ['qetaa_id' => $qetaa->QetaaID]) }}"
                    class="rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ (int) $filterQetaaId === (int) $qetaa->QetaaID ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800' }}">
                    {{ $qetaa->QetaaName }}
                </a>
            @endforeach
        </div>

        @forelse ($grouped as $qetaaId => $sectorPlans)
            @php
                $sectorName = $sectorPlans->first()->QetaaName;
                $activeCount = $sectorPlans->where('IsActiveFlag', 1)->count();
            @endphp
            <section class="mb-8">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $sectorName }}</h2>
                        <span
                            class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            {{ __(':count plans', ['count' => $sectorPlans->count()]) }}
                        </span>
                    </div>
                    @if ($activeCount === 1)
                        <span
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            {{ __('One active plan') }}
                        </span>
                    @elseif ($activeCount === 0)
                        <span class="text-xs font-medium text-amber-700 dark:text-amber-300">
                            {{ __('No active plan yet') }}
                        </span>
                    @endif
                </div>

                <div class="space-y-3">
                    @foreach ($sectorPlans as $plan)
                        <article
                            class="group relative overflow-hidden rounded-2xl border bg-white p-4 shadow-sm transition hover:shadow-md dark:bg-slate-900 sm:p-5 {{ (int) $plan->IsActiveFlag === 1 ? 'border-emerald-300 dark:border-emerald-700' : 'border-slate-200 dark:border-slate-800' }}">
                            @if ((int) $plan->IsActiveFlag === 1)
                                <div class="absolute inset-y-0 start-0 w-1 bg-emerald-500" aria-hidden="true"></div>
                            @endif

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 ps-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-base font-semibold text-slate-900 dark:text-slate-50">
                                            {{ $plan->PlanName }}
                                        </h3>
                                        @if ((int) $plan->IsActiveFlag === 1)
                                            <span
                                                class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                                                {{ __('Active') }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                                {{ __('Inactive') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-3 text-xs text-slate-500 dark:text-slate-400">
                                        <span>
                                            {{ __(':count lectures', ['count' => $plan->LectureCount]) }}
                                        </span>
                                        <span aria-hidden="true">·</span>
                                        <span>{{ __('Sort order') }}: {{ $plan->SortOrder }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('curriculum-plan.edit', $plan->PlanID) }}"
                                        class="inline-flex h-9 items-center rounded-lg bg-slate-900 px-3 text-xs font-semibold text-white transition hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">
                                        {{ __('Edit & lectures') }}
                                    </a>

                                    @if ((int) $plan->IsActiveFlag === 1)
                                        <form method="POST"
                                            action="{{ route('curriculum-plan.deactivate', $plan->PlanID) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex h-9 items-center rounded-lg border border-amber-300 bg-amber-50 px-3 text-xs font-semibold text-amber-800 transition hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200 dark:hover:bg-amber-900/40">
                                                {{ __('Deactivate') }}
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST"
                                            action="{{ route('curriculum-plan.activate', $plan->PlanID) }}">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex h-9 items-center rounded-lg border border-emerald-300 bg-emerald-50 px-3 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200 dark:hover:bg-emerald-900/40">
                                                {{ __('Activate') }}
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('curriculum-plan.delete', $plan->PlanID) }}"
                                        class="inline-flex h-9 items-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-red-600 transition hover:border-red-200 hover:bg-red-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-red-950/30">
                                        {{ __('Delete') }}
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div
                class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900">
                <div
                    class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                    {{ __('No curriculum plans yet.') }}
                </h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Create the first syllabus plan for a sector, then choose lectures and activate it.') }}
                </p>
                <a href="{{ route('curriculum-plan.create') }}"
                    class="mt-6 inline-flex h-11 items-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white hover:bg-emerald-700">
                    {{ __('Add curriculum plan') }}
                </a>
            </div>
        @endforelse
    </div>
@endsection
