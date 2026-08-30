@extends('layouts.app', ['pageTitle' => __('Migrate enrolments')])

@section('content')
    @php
        $palette = [
            'bg-rose-600 hover:bg-rose-700',
            'bg-sky-600 hover:bg-sky-700',
            'bg-amber-500 hover:bg-amber-600',
            'bg-emerald-600 hover:bg-emerald-700',
            'bg-violet-600 hover:bg-violet-700',
            'bg-teal-600 hover:bg-teal-700',
            'bg-orange-600 hover:bg-orange-700',
            'bg-indigo-600 hover:bg-indigo-700',
            'bg-slate-800 hover:bg-slate-900',
            'bg-lime-600 hover:bg-lime-700',
            'bg-cyan-600 hover:bg-cyan-700',
        ];
    @endphp

    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">
        <section
            class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6">
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-50">
                {{ __('Migrate enrolments') }}
            </h2>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                {{ __('Transfer approved new enrolments into the main system.') }}
            </p>
            <div
                class="mt-4 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                {{ __('Approved waiting:') }} {{ number_format($totalApproved) }}
            </div>
        </section>

        <section class="space-y-3">
            <form method="POST" action="{{ route('person.migrate-new-enrolments-all') }}"
                onsubmit="return confirm(@json(__('Migrate all approved enrolments into the main system?')))">
                @csrf
                <button type="submit"
                    class="block w-full rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-center font-extrabold text-base sm:text-lg px-5 py-5 shadow-sm transition-colors duration-200">
                    {{ __('Migrate all approved people into the main system') }}
                    <span class="mt-1 block text-sm font-semibold text-emerald-100">
                        {{ __('Total:') }} {{ number_format($totalApproved) }}
                    </span>
                </button>
            </form>
        </section>

        <section class="space-y-3">
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">
                {{ __('Migrate by sector') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($qetaas as $index => $qetaa)
                    @php
                        $color = $palette[$index % count($palette)];
                    @endphp
                    <form method="POST" action="{{ route('person.migrate-new-enrolments', $qetaa->QetaaID) }}"
                        onsubmit="return confirm(@json(__('Migrate approved enrolments for this sector?')))">
                        @csrf
                        <button type="submit"
                            class="block w-full rounded-2xl {{ $color }} text-white px-5 py-4 shadow-sm transition-colors duration-200 text-start">
                            <div class="font-extrabold text-base leading-snug">
                                {{ $qetaa->QetaaName }}
                            </div>
                            <div class="mt-2 text-sm font-semibold text-white/90">
                                {{ __('Approved:') }} {{ number_format($qetaa->approved_count) }}
                            </div>
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
@endsection
