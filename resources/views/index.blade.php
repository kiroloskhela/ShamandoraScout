@extends('layouts.app', ['pageTitle' => __('Dashboard')])

@section('content')
    @php
        $user = Auth::user();
        $displayName = trim(($user->FirstName ?? '') . ' ' . ($user->SecondName ?? ''));
        $eventsCount = collect($events ?? [])->unique('EventID')->count();
    @endphp

    <div class="space-y-6">
        {{-- Welcome --}}
        <section
            class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-gradient-to-l from-emerald-50/80 via-white to-white dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-900 px-5 py-5 sm:px-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                        {{ __('Dashboard') }}
                    </p>
                    <h2 class="mt-1 text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-50">
                        {{ __('Welcome, :name', ['name' => $displayName !== '' ? $displayName : __('Scout')]) }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        {{ __('Your shortcuts and upcoming events in one place.') }}
                    </p>
                </div>
                @if (!empty($user->ShamandoraCode))
                    <div
                        class="inline-flex items-center self-start sm:self-center rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                        {{ $user->ShamandoraCode }}
                    </div>
                @endif
            </div>
        </section>

        {{-- Metrics --}}
        <section>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-card-stat href="{{ route('person.index', ['id' => Auth::id()]) }}"
                    title="{{ __('Current members count') }}" :count="$personsCount ?? 0" color="blue" compact>
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>

                <x-card-stat href="#calendar" title="{{ __('Events') }}" :count="$eventsCount" color="emerald"
                    compact>
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>
        </section>

        {{-- Quick actions --}}
        <section class="space-y-3">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ __('Quick actions') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Jump to common pages') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <x-card-stat href="{{ route('attendance.manage', ['id' => Auth::id()]) }}"
                    title="{{ __('Member attendance') }}" color="indigo" compact>
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>

                <x-card-stat href="{{ route('custody_requests.my', ['id' => Auth::id()]) }}"
                    title="{{ __('Custody booking requests') }}" color="yellow" compact>
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V7a2 2 0 00-2-2h-3l-2-2-2 2H8a2 2 0 00-2 2v6m14 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>

                <x-card-stat href="{{ route('place_bookings.my', ['id' => Auth::id()]) }}"
                    title="{{ __('Place booking requests') }}" color="pink" compact>
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10l9-6 9 6v8a2 2 0 01-2 2h-2a2 2 0 01-2-2v-3H9v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-8z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>

                <x-card-stat href="{{ route('profile.show') }}" title="{{ __('My profile') }}" color="rose" compact>
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A4 4 0 017 16h10a4 4 0 011.879.496M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>
        </section>

        {{-- Calendar --}}
        <section id="calendar" class="scroll-mt-24 space-y-3">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ __('Calendar') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Browse your upcoming events') }}</p>
            </div>
            <x-calendar :events="$events" />
        </section>
    </div>
@endsection
