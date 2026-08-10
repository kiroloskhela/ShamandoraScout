@extends('layouts.app', ['pageTitle' => __('Edit curriculum plan')])

@section('content')
    @php
        $initialSelected = collect(old('curricula_ids', $selectedIds))->map(fn ($id) => (int) $id)->values()->all();
        $lecturePayload = $curricula
            ->map(fn ($item) => [
                'id' => (int) $item->CurriculaID,
                'name' => (string) $item->CurriculaName,
                'category' => (string) ($item->CurriculaCategoryName ?? ''),
                'marhala' => (string) ($item->MarhalaName ?? ''),
            ])
            ->values()
            ->all();
    @endphp

    <div class="mx-auto max-w-5xl px-4 py-8"
        x-data="curriculumLecturePicker(@js($lecturePayload), @js($initialSelected))">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('curriculum-plan.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition hover:text-emerald-700 dark:text-slate-400 dark:hover:text-emerald-300">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('Back to curriculum plans') }}
                </a>
                <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ __('Edit curriculum plan') }}
                </h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    {{ __('Sector') }}:
                    <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $qetaa->QetaaName ?? $plan->QetaaID }}</span>
                    @if ((int) $plan->IsActive === 1)
                        <span
                            class="ms-2 inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                            {{ __('Active') }}
                        </span>
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if ((int) $plan->IsActive === 1)
                    <form method="POST" action="{{ route('curriculum-plan.deactivate', $plan->PlanID) }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex h-10 items-center rounded-xl border border-amber-300 bg-amber-50 px-4 text-sm font-semibold text-amber-800 transition hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200">
                            {{ __('Deactivate') }}
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('curriculum-plan.activate', $plan->PlanID) }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex h-10 items-center rounded-xl border border-emerald-300 bg-emerald-50 px-4 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200">
                            {{ __('Activate') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div
                class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200"
                role="status">
                {{ session('success') }}
            </div>
        @endif

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

        <form method="POST" action="{{ route('curriculum-plan.update', $plan->PlanID) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    {{ __('Plan details') }}
                </h2>
                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <label for="plan_name" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            {{ __('Plan name') }}
                        </label>
                        <input id="plan_name" type="text" name="plan_name" required
                            value="{{ old('plan_name', $plan->PlanName) }}"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
                    </div>
                    <div>
                        <label for="sort_order" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">
                            {{ __('Sort order') }}
                        </label>
                        <input id="sort_order" type="number" name="sort_order" min="0" max="9999"
                            value="{{ old('sort_order', $plan->SortOrder) }}"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" />
                    </div>
                </div>
            </div>

            {{-- Lecture bank --}}
            <div
                class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                            {{ __('Lecture bank') }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ __('Select lectures for this plan bank.') }}
                        </p>
                    </div>
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
                        <span x-text="selectedCount"></span>
                        <span>{{ __('selected') }}</span>
                    </div>
                </div>

                <div class="space-y-4 px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                            </svg>
                            <label for="lecture-search" class="sr-only">{{ __('Search lectures') }}</label>
                            <input id="lecture-search" type="search" x-model="query"
                                placeholder="{{ __('Search by name, category, or stage…') }}"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pe-3 ps-10 text-sm shadow-sm transition focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/30 dark:border-slate-700 dark:bg-slate-950 dark:focus:bg-slate-950" />
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="selectFiltered()"
                                class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                {{ __('Select visible') }}
                            </button>
                            <button type="button" @click="clearAll()"
                                class="inline-flex h-11 items-center rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                {{ __('Clear selection') }}
                            </button>
                        </div>
                    </div>

                    <template x-if="filtered.length === 0">
                        <div
                            class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            <span x-show="lectures.length === 0">{{ __('No lectures available.') }}</span>
                            <span x-show="lectures.length > 0">{{ __('No lectures match your search.') }}</span>
                        </div>
                    </template>

                    <div class="grid max-h-[28rem] gap-2 overflow-y-auto pe-1 sm:grid-cols-2"
                        x-show="filtered.length > 0">
                        <template x-for="lecture in filtered" :key="lecture.id">
                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-xl border p-3 transition hover:border-emerald-300 hover:bg-emerald-50/40 dark:hover:border-emerald-700 dark:hover:bg-emerald-950/20"
                                :class="isSelected(lecture.id)
                                    ? 'border-emerald-400 bg-emerald-50/70 dark:border-emerald-600 dark:bg-emerald-950/30'
                                    : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950/40'">
                                <input type="checkbox" class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                    :value="lecture.id" :checked="isSelected(lecture.id)"
                                    @change="toggle(lecture.id)" name="curricula_ids[]">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-slate-900 dark:text-slate-100"
                                        x-text="lecture.name"></span>
                                    <span class="mt-0.5 block truncate text-xs text-slate-500 dark:text-slate-400"
                                        x-text="[lecture.category, lecture.marhala].filter(Boolean).join(' — ')"></span>
                                </span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <div
                class="sticky bottom-4 z-10 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/95 px-4 py-3 shadow-lg backdrop-blur dark:border-slate-700 dark:bg-slate-900/95">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    <span class="font-semibold text-slate-800 dark:text-slate-100" x-text="selectedCount"></span>
                    {{ __('lectures in this plan') }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('curriculum-plan.index') }}"
                        class="inline-flex h-11 items-center rounded-xl px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        class="inline-flex h-11 items-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                        {{ __('Save plan') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('curriculumLecturePicker', (lectures = [], selectedIds = []) => ({
                lectures,
                selected: new Set((selectedIds || []).map(Number)),
                query: '',
                get filtered() {
                    const q = this.query.trim().toLowerCase();
                    if (!q) {
                        return this.lectures;
                    }

                    return this.lectures.filter((lecture) => {
                        const hay = [lecture.name, lecture.category, lecture.marhala]
                            .join(' ')
                            .toLowerCase();

                        return hay.includes(q);
                    });
                },
                get selectedCount() {
                    return this.selected.size;
                },
                isSelected(id) {
                    return this.selected.has(Number(id));
                },
                toggle(id) {
                    const key = Number(id);
                    if (this.selected.has(key)) {
                        this.selected.delete(key);
                    } else {
                        this.selected.add(key);
                    }
                    this.selected = new Set(this.selected);
                },
                selectFiltered() {
                    this.filtered.forEach((lecture) => this.selected.add(Number(lecture.id)));
                    this.selected = new Set(this.selected);
                },
                clearAll() {
                    this.selected = new Set();
                },
            }));
        });
    </script>
@endsection
