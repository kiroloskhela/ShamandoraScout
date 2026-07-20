@extends('layouts.app', ['pageTitle' => __('Profile')])

@section('title', __('Profile | Shamandora'))

@section('content')
@php
    $p = $person;
    $fullName = trim(collect([$p->FirstName ?? '', $p->SecondName ?? '', $p->ThirdName ?? '', $p->FourthName ?? ''])->filter()->implode(' ')) ?: __('User');
    $code = $p->ShamandoraCode ?? null;
    $photoPath = $p->PersonalImagePath ?? null;
    $photoUrl = null;
    if ($photoPath) {
        $photoUrl = preg_match('#^https?://#i', $photoPath)
            ? $photoPath
            : asset('storage/' . ltrim(preg_replace('#^storage/#', '', $photoPath), '/'));
    }
    $initials = strtoupper(mb_substr($p->FirstName ?? 'م', 0, 1) . mb_substr($p->SecondName ?? '', 0, 1));
    $addressParts = array_filter([
        $p->BuildingNumber ? __('Building') . ' ' . $p->BuildingNumber : null,
        $p->FloorNumber ? __('Floor') . ' ' . $p->FloorNumber : null,
        $p->AppartmentNumber ? __('Apartment') . ' ' . $p->AppartmentNumber : null,
        $p->MainStreetName,
        $p->SubStreetName,
        $p->NearestLandmark,
        $p->ManteqaName,
        $p->DistrictName,
    ]);
    $address = $addressParts ? implode('، ', $addressParts) : null;
    $emergency = $p->FatherMobileNumber ?: ($p->MotherMobileNumber ?: null);
    $emergencyLabel = $p->FatherMobileNumber ? __('Father') : ($p->MotherMobileNumber ? __('Mother') : null);
    $attendanceRate = $attendance['summary']['rate'] ?? 0;
    $badgeName = $p->EgazetBetakatTaqaddomName ?? null;
    $qrPayload = urlencode($code ?: ('PID-' . ($p->PersonID ?? '')));
    $val = fn ($v, $fallback = '—') => ($v !== null && $v !== '') ? $v : $fallback;
@endphp

<style>
    .profile-page {
        --brand: #0f766e;
        --brand-deep: #0b5f59;
        --brand-soft: #ecfdf8;
        --ink: #0f172a;
        --muted: #64748b;
        --line: #e2e8f0;
        --card: #ffffff;
        --page: #f1f5f9;
    }

    [x-cloak] { display: none !important; }

    .profile-page .hero-banner {
        background: linear-gradient(135deg, #0b5f59 0%, #0f766e 45%, #14b8a6 100%);
    }

    .profile-page .tab-btn {
        transition: color .2s ease, border-color .2s ease;
    }

    .profile-page .tab-btn[aria-selected="true"] {
        color: var(--brand);
        border-color: var(--brand);
        font-weight: 700;
    }

    .profile-page .stat-ring {
        background: conic-gradient(var(--brand) calc(var(--pct) * 1%), #e2e8f0 0);
    }

    .profile-page .stat-ring-danger {
        background: conic-gradient(#ef4444 calc(var(--pct) * 1%), #fee2e2 0);
    }

    @media (prefers-reduced-motion: no-preference) {
        .profile-page .fade-in {
            animation: profileFade .35s ease-out;
        }
    }

    @keyframes profileFade {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: none; }
    }
</style>

@php
    $activeTab = request('tab', 'personal');
    if (! in_array($activeTab, ['personal', 'study', 'seasons', 'custody', 'bookings'], true)) {
        $activeTab = 'personal';
    }
    $exams = $seasonActivity['exams'] ?? collect();
    $finances = $seasonActivity['finances'] ?? collect();
@endphp
<div class="profile-page -mx-2 sm:mx-0" x-data="{ tab: @js($activeTab) }">
    @if (session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-200 dark:border-slate-700 bg-emerald-50 dark:bg-emerald-900/40 px-4 py-3 text-emerald-800 dark:text-emerald-200 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Hero --}}
    <section class="overflow-hidden rounded-3xl bg-white dark:bg-slate-900 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700 mb-6">
        <div class="hero-banner h-28 sm:h-32"></div>
        <div class="relative px-5 sm:px-8 pb-6 -mt-14">
            <div class="flex flex-col lg:flex-row lg:items-end gap-5 lg:gap-8">
                <div class="flex flex-col items-start gap-3 shrink-0">
                    <div class="relative">
                        @if ($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $fullName }}"
                                class="h-32 w-28 sm:h-36 sm:w-32 rounded-2xl object-cover ring-4 ring-white dark:ring-slate-800 shadow-lg bg-slate-100 dark:bg-slate-800">
                        @else
                            <div class="h-32 w-28 sm:h-36 sm:w-32 rounded-2xl ring-4 ring-white dark:ring-slate-800 shadow-lg flex items-center justify-center text-3xl font-bold text-white"
                                style="background: linear-gradient(145deg, #0f766e, #134e4a);">
                                {{ $initials }}
                            </div>
                        @endif
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 dark:bg-teal-900/40 text-teal-800 dark:text-teal-200 px-3 py-1 text-xs font-bold ring-1 ring-teal-200 dark:ring-slate-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                        {{ __('Active') }}
                    </span>
                </div>

                <div class="flex-1 min-w-0 pt-2 lg:pb-2">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">{{ $fullName }}</h1>
                        @if ($code)
                            <span class="font-mono text-sm sm:text-base text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg" dir="ltr">#{{ $code }}</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2 mt-3">
                        @if (!empty($p->RotbaName))
                            <span class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 px-3 py-1 text-xs font-bold ring-1 ring-indigo-100 dark:ring-slate-700">
                                {{ $p->RotbaName }}
                            </span>
                        @endif
                        @if (!empty($p->QetaaName))
                            <span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-900/40 text-rose-700 dark:text-rose-200 px-3 py-1 text-xs font-bold ring-1 ring-rose-100 dark:ring-slate-700">
                                {{ $p->QetaaName }}
                            </span>
                        @endif
                        @if ($badgeName)
                            <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 px-3 py-1 text-xs font-bold ring-1 ring-amber-100 dark:ring-slate-700">
                                {{ $badgeName }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="lg:pb-2">
                    <a href="{{ route('profile.edit') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 hover:bg-teal-800 active:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 text-white font-bold px-5 py-3 text-sm shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536M4 20h4.586a1 1 0 00.707-.293l9.414-9.414a2 2 0 000-2.828l-3.172-3.172a2 2 0 00-2.828 0L4.293 14.707A1 1 0 004 15.414V20z" />
                        </svg>
                        {{ __('Edit profile') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Tabs --}}
    <nav class="mb-5 flex gap-1 overflow-x-auto border-b border-slate-200 dark:border-slate-700" role="tablist" aria-label="{{ __('Profile sections') }}">
        @foreach ([
            'personal' => __('Personal data'),
            'study' => __('Study data'),
            'seasons' => __('Seasons'),
            'custody' => __('Custody'),
            'bookings' => __('Bookings'),
        ] as $key => $label)
            <button type="button" role="tab"
                class="tab-btn shrink-0 px-4 py-3 text-sm text-slate-500 dark:text-slate-400 border-b-2 border-transparent hover:text-teal-700 dark:hover:text-teal-400"
                :aria-selected="tab === '{{ $key }}'"
                @click="tab = '{{ $key }}'">
                {{ $label }}
            </button>
        @endforeach
    </nav>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-8 space-y-5">
            {{-- Personal --}}
            <div x-show="tab === 'personal'" x-cloak class="fade-in space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                        <div class="flex items-center gap-3 mb-5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 dark:bg-teal-900/40 text-teal-700 dark:text-teal-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </span>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ __('Contact information') }}</h2>
                        </div>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Phone number') }}</dt>
                                <dd class="text-base font-bold text-slate-900 dark:text-slate-100" dir="ltr">{{ $val($p->PersonPersonalMobileNumber ?? null) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Email') }}</dt>
                                <dd class="text-base font-bold text-slate-900 dark:text-slate-100 break-all" dir="ltr">{{ $val($p->PersonalEmail ?? null) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Emergency phone') }}</dt>
                                <dd class="text-base font-bold text-slate-900 dark:text-slate-100">
                                    <span dir="ltr">{{ $val($emergency) }}</span>
                                    @if ($emergencyLabel)
                                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">({{ $emergencyLabel }})</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </article>

                    <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                        <div class="flex items-center gap-3 mb-5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ __('Legal information') }}</h2>
                        </div>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('National ID') }}</dt>
                                <dd class="text-base font-bold text-slate-900 dark:text-slate-100 font-mono" dir="ltr">{{ $val($p->RaqamQawmy ?? null) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Date of birth') }}</dt>
                                <dd class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $val($p->DateOfBirth ?? null) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Address') }}</dt>
                                <dd class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-relaxed">{{ $val($address) }}</dd>
                            </div>
                        </dl>
                    </article>
                </div>

                <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </span>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ __('Medical and skills information') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Blood type') }}</div>
                            <div class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $val($p->BloodTypeName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Scout rank') }}</div>
                            <div class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $val($p->RotbaName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Joining year') }}</div>
                            <div class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $val($p->ScoutJoiningYear ?? null) }}</div>
                        </div>
                    </div>
                </article>
            </div>

            {{-- Study --}}
            <div x-show="tab === 'study'" x-cloak class="fade-in">
                <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-5">{{ __('Study and work data') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Stage / year') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->SanaMarhalaName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('School') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->SchoolName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('University') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->UniversityName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Faculty') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->FacultyName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Job') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->JobName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Workplace') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->WorkPlace ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Spiritual father') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->SpiritualFatherName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Spiritual father church') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->SpiritualFatherChurchName ?? null) }}</div>
                        </div>
                    </div>
                </article>
            </div>

            {{-- Seasons --}}
            <div x-show="tab === 'seasons'" x-cloak class="fade-in space-y-4">
                <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ __('Season activity') }}</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Attendance, absences, exam scores and event finance for the selected season.') }}</p>
                        </div>
                        <form method="GET" action="{{ route('profile.show') }}" class="flex items-center gap-2">
                            <input type="hidden" name="tab" value="seasons">
                            <label for="profile-season" class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Season') }}</label>
                            <select id="profile-season" name="season" onchange="this.form.submit()"
                                class="rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 ps-3 py-2 text-sm font-semibold min-w-[12rem]">
                                @forelse ($seasons as $season)
                                    <option value="{{ $season->SeasonID }}" @selected((int) $selectedSeasonId === (int) $season->SeasonID)>
                                        {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                                        @if ((int) ($season->IsActive ?? 0) === 1) — {{ __('Active') }} @endif
                                    </option>
                                @empty
                                    <option value="">{{ __('No seasons') }}</option>
                                @endforelse
                            </select>
                        </form>
                    </div>
                </article>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ([
                        ['label' => __('Total events'), 'value' => $attendance['summary']['total'], 'class' => 'bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100'],
                        ['label' => __('Present'), 'value' => $attendance['summary']['present'], 'class' => 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-200'],
                        ['label' => __('Absent'), 'value' => $attendance['summary']['absent'], 'class' => 'bg-rose-50 dark:bg-rose-900/40 text-rose-800 dark:text-rose-200'],
                        ['label' => __('Excused'), 'value' => $attendance['summary']['excused'], 'class' => 'bg-amber-50 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200'],
                    ] as $stat)
                        <div class="rounded-2xl {{ $stat['class'] }} p-4 text-center ring-1 ring-black/5 dark:ring-slate-700">
                            <div class="text-2xl font-bold">{{ $stat['value'] }}</div>
                            <div class="text-xs font-semibold mt-1 opacity-80">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>

                <article class="rounded-2xl bg-white dark:bg-slate-900 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-bold text-slate-900 dark:text-slate-100">{{ __('Attendance') }}</div>
                    @forelse ($attendance['events'] as $ev)
                        <div class="px-5 py-3.5 flex items-center justify-between gap-3 border-b border-slate-50 dark:border-slate-700 last:border-0">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 dark:text-slate-100 truncate">{{ $ev->EventName }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $ev->EventStartDate }}</div>
                            </div>
                            @php
                                $statusMap = [
                                    'present' => [__('Present'), 'bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-200'],
                                    'excused' => [__('Excused'), 'bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-200'],
                                    'absent' => [__('Absent'), 'bg-rose-50 dark:bg-rose-900/40 text-rose-700 dark:text-rose-200'],
                                ];
                                [$stLabel, $stClass] = $statusMap[$ev->Status] ?? ['—', 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'];
                            @endphp
                            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $stClass }}">{{ $stLabel }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-slate-500 dark:text-slate-400 text-sm">{{ __('No attendance records for this season.') }}</div>
                    @endforelse
                </article>

                <article class="rounded-2xl bg-white dark:bg-slate-900 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-bold text-slate-900 dark:text-slate-100">{{ __('Exam scores') }}</div>
                    @forelse ($exams as $exam)
                        <div class="px-5 py-3.5 flex flex-wrap items-center justify-between gap-2 border-b border-slate-50 dark:border-slate-700 last:border-0">
                            <div>
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $exam->ExamDate ?? '—' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $exam->QetaaName ?? '—' }} · {{ $exam->SanaMarhalaName ?? '—' }}</div>
                            </div>
                            <div class="text-sm font-bold text-teal-700 dark:text-teal-300">
                                {{ __('Theoretical') }} {{ $exam->TheoreticalMark }} · {{ __('Practical') }} {{ $exam->PracticalMark }}
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-slate-500 dark:text-slate-400 text-sm">{{ __('No exam scores for this season.') }}</div>
                    @endforelse
                </article>

                <article class="rounded-2xl bg-white dark:bg-slate-900 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 font-bold text-slate-900 dark:text-slate-100">{{ __('Event finance') }}</div>
                    @forelse ($finances as $finance)
                        <div class="px-5 py-3.5 flex flex-wrap items-center justify-between gap-3 border-b border-slate-50 dark:border-slate-700 last:border-0">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 dark:text-slate-100 truncate">{{ $finance->EventName }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $finance->EventStartDate }}</div>
                            </div>
                            <div class="text-xs text-end">
                                <div>{{ __('Paid') }}: {{ $finance->AmountPaid }}</div>
                                <div>{{ __('Remaining') }}: {{ $finance->RemainingAmount }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-slate-500 dark:text-slate-400 text-sm">{{ __('No event finance records for this season.') }}</div>
                    @endforelse
                </article>
            </div>

            {{-- Custody --}}
            <div x-show="tab === 'custody'" x-cloak class="fade-in">
                <article class="rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700 text-center sm:text-right sm:flex sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ __('Custody requests') }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('You have :count registered custody request(s).', ['count' => $custodyCount]) }}</p>
                    </div>
                    <a href="{{ route('custody_requests.my') }}"
                        class="mt-4 sm:mt-0 inline-flex items-center justify-center rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-bold px-5 py-3 text-sm transition">
                        {{ __('View my requests') }}
                    </a>
                </article>
            </div>

            {{-- Bookings --}}
            <div x-show="tab === 'bookings'" x-cloak class="fade-in">
                <article class="rounded-2xl bg-white dark:bg-slate-900 p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700 text-center sm:text-right sm:flex sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ __('Bookings') }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('You have :count registered place booking(s).', ['count' => $bookingCount]) }}</p>
                    </div>
                    <a href="{{ route('place_bookings.my') }}"
                        class="mt-4 sm:mt-0 inline-flex items-center justify-center rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-bold px-5 py-3 text-sm transition">
                        {{ __('View my bookings') }}
                    </a>
                </article>
            </div>
        </div>

        {{-- Side panel --}}
        <aside class="xl:col-span-4 space-y-5">
            <div class="rounded-3xl overflow-hidden shadow-lg text-white"
                style="background: linear-gradient(160deg, #0b5f59 0%, #0f766e 55%, #115e59 100%);">
                <div class="p-5 text-center">
                    <img src="{{ asset('img/shamandora.webp') }}" alt="" class="mx-auto h-14 w-14 object-contain drop-shadow mb-2 bg-white/10 rounded-full p-1">
                    <div class="text-xs font-semibold tracking-wide text-teal-100/90 uppercase">{{ __('Scout identity') }}</div>
                    <div class="text-sm font-bold mt-1">{{ __('Shamandora Sea Scouts') }}</div>
                </div>
                <div class="mx-5 mb-4 rounded-2xl bg-white dark:bg-slate-800 p-4 text-center shadow-inner">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ $qrPayload }}"
                        alt="QR Code" class="mx-auto h-36 w-36 rounded-lg" width="160" height="160" loading="lazy">
                    <div class="mt-3 font-mono text-sm font-bold text-slate-800 dark:text-slate-100" dir="ltr">{{ $code ? '#'.$code : '—' }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 truncate px-2">{{ $fullName }}</div>
                </div>
                <div class="px-5 pb-5 grid grid-cols-2 gap-3 text-center text-xs">
                    <div class="rounded-xl bg-white/10 px-2 py-2.5">
                        <div class="text-teal-100/80">{{ __('Joining year') }}</div>
                        <div class="font-bold mt-0.5">{{ $val($p->ScoutJoiningYear ?? null) }}</div>
                    </div>
                    <div class="rounded-xl bg-white/10 px-2 py-2.5">
                        <div class="text-teal-100/80">{{ __('Sector') }}</div>
                        <div class="font-bold mt-0.5 truncate">{{ $val($p->QetaaName ?? null) }}</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-2xl bg-white dark:bg-slate-900 p-4 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700 text-center">
                    <div class="mx-auto h-20 w-20 rounded-full p-[6px] stat-ring" style="--pct: {{ min(100, ($badgeName ? 70 : 20)) }}">
                        <div class="h-full w-full rounded-full bg-white dark:bg-slate-900 flex items-center justify-center">
                            <span class="text-xl font-bold text-teal-800 dark:text-teal-200">{{ $badgeName ? '1' : '0' }}</span>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('Badge / card') }}</div>
                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 truncate px-1">{{ $val($badgeName, __('None')) }}</div>
                </div>
                <div class="rounded-2xl bg-white dark:bg-slate-900 p-4 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700 text-center">
                    <div class="mx-auto h-20 w-20 rounded-full p-[6px] {{ $attendanceRate >= 75 ? 'stat-ring' : 'stat-ring-danger' }}" style="--pct: {{ min(100, (float) $attendanceRate) }}">
                        <div class="h-full w-full rounded-full bg-white dark:bg-slate-900 flex items-center justify-center">
                            <span class="text-lg font-bold {{ $attendanceRate >= 75 ? 'text-teal-800 dark:text-teal-200' : 'text-rose-600 dark:text-rose-300' }}">{{ $attendanceRate }}%</span>
                        </div>
                    </div>
                    <div class="mt-3 text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('Season attendance') }}</div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
