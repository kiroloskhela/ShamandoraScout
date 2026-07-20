@extends('layouts.app', ['pageTitle' => __('Person profile')])

@section('content')
@php
    $p = $person;
    $fullName = trim(collect([$p->FirstName ?? '', $p->SecondName ?? '', $p->ThirdName ?? '', $p->FourthName ?? ''])->filter()->implode(' ')) ?: __('Person');
    $code = $p->ShamandoraCode ?? null;
    $buildImgUrl = function ($path) {
        if (!$path) {
            return null;
        }
        $path = trim($path);
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'uploads/') || str_starts_with($path, 'img/')) {
            return asset($path);
        }
        return asset('storage/' . ltrim($path, '/'));
    };
    $photoUrl = $buildImgUrl($p->PersonalImagePath ?? null);
    $scoutUrl = $buildImgUrl($p->ScoutImagePath ?? null);
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
    $badgeName = $p->EgazetBetakatTaqaddomName ?? null;
    $attendance = $seasonActivity['attendance'] ?? ['events' => collect(), 'summary' => ['total' => 0, 'present' => 0, 'absent' => 0, 'excused' => 0, 'rate' => 0]];
    $exams = $seasonActivity['exams'] ?? collect();
    $finances = $seasonActivity['finances'] ?? collect();
    $attendanceRate = $attendance['summary']['rate'] ?? 0;
    $activeTab = request('tab', 'personal');
    if (! in_array($activeTab, ['personal', 'study', 'scout', 'questions', 'seasons'], true)) {
        $activeTab = 'personal';
    }
    $val = fn ($v, $fallback = '—') => ($v !== null && $v !== '') ? $v : $fallback;
@endphp

<style>
    .person-profile-page { --brand: #0f766e; }
    [x-cloak] { display: none !important; }
    .person-profile-page .hero-banner {
        background: linear-gradient(135deg, #0b5f59 0%, #0f766e 45%, #14b8a6 100%);
    }
    .person-profile-page .tab-btn { transition: color .2s ease, border-color .2s ease; }
    .person-profile-page .tab-btn[aria-selected="true"] {
        color: var(--brand);
        border-color: var(--brand);
        font-weight: 700;
    }
    .person-profile-page .stat-ring {
        background: conic-gradient(var(--brand) calc(var(--pct) * 1%), #e2e8f0 0);
    }
    .person-profile-page .stat-ring-danger {
        background: conic-gradient(#ef4444 calc(var(--pct) * 1%), #fee2e2 0);
    }
    @media (prefers-reduced-motion: no-preference) {
        .person-profile-page .fade-in { animation: personProfileFade .35s ease-out; }
    }
    @keyframes personProfileFade {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: none; }
    }
</style>

<div class="person-profile-page -mx-2 sm:mx-0"
    x-data="{ tab: @js($activeTab) }"
    x-init="
        if (tab === 'seasons') {
            $nextTick(() => document.getElementById('season-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
        }
    ">
    @if (session('status') || session('success'))
        <div class="mb-4 rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/40 px-4 py-3 text-emerald-800 dark:text-emerald-200 text-sm font-semibold">
            {{ session('status') ?? session('success') }}
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
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 dark:bg-teal-900/40 text-teal-800 dark:text-teal-200 px-3 py-1 text-xs font-bold ring-1 ring-teal-200 dark:ring-teal-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                        #{{ $p->PersonID }}
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
                            <span class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-200 px-3 py-1 text-xs font-bold ring-1 ring-indigo-100 dark:ring-indigo-800">{{ $p->RotbaName }}</span>
                        @endif
                        @if (!empty($p->QetaaName))
                            <span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-900/40 text-rose-700 dark:text-rose-200 px-3 py-1 text-xs font-bold ring-1 ring-rose-100 dark:ring-rose-800">{{ $p->QetaaName }}</span>
                        @endif
                        @if (!empty($p->SanaMarhalaName))
                            <span class="inline-flex items-center rounded-full bg-sky-50 dark:bg-sky-900/40 text-sky-800 dark:text-sky-200 px-3 py-1 text-xs font-bold ring-1 ring-sky-100 dark:ring-sky-800">{{ $p->SanaMarhalaName }}</span>
                        @endif
                        @if ($badgeName)
                            <span class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 px-3 py-1 text-xs font-bold ring-1 ring-amber-100 dark:ring-amber-800">{{ $badgeName }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 lg:pb-2">
                    @if (!empty($canEdit))
                        <a href="{{ route('person.edit', $p->PersonID) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-bold px-5 py-3 text-sm shadow-sm transition">
                            {{ __('Edit') }}
                        </a>
                    @endif
                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('person.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 font-bold px-5 py-3 text-sm transition">
                        {{ __('Back') }}
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
            'scout' => __('Scout information'),
            'questions' => __('Sector questions'),
            'seasons' => __('Seasons'),
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
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-5">{{ __('Contact information') }}</h2>
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
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Landline') }}</dt>
                                <dd class="text-base font-bold text-slate-900 dark:text-slate-100" dir="ltr">{{ $val($p->HomePhoneNumber ?? null) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('WhatsApp on primary number') }}</dt>
                                <dd class="text-base font-bold text-slate-900 dark:text-slate-100">
                                    {{ (string) ($p->IsOPersonalPhoneNumberHavingWhatsapp ?? '') === '1' ? __('Yes') : __('No') }}
                                </dd>
                            </div>
                        </dl>
                    </article>

                    <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-5">{{ __('Legal information') }}</h2>
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
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Gender') }}</dt>
                                <dd class="text-base font-bold text-slate-900 dark:text-slate-100">{{ ($p->Gender ?? '') === 'Female' ? __('Female') : __('Male') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Blood type') }}</dt>
                                <dd class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $val($p->BloodTypeName ?? null) }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Address') }}</dt>
                                <dd class="text-sm font-semibold text-slate-800 dark:text-slate-100 leading-relaxed">{{ $val($address) }}</dd>
                            </div>
                        </dl>
                    </article>
                </div>

                <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-5">{{ __('Social links') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Facebook link') }}</div>
                            <div class="font-semibold text-slate-900 dark:text-slate-100 break-all" dir="ltr">{{ $val($p->FacebookProfileURL ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Instagram link') }}</div>
                            <div class="font-semibold text-slate-900 dark:text-slate-100 break-all" dir="ltr">{{ $val($p->InstagramProfileURL ?? null) }}</div>
                        </div>
                    </div>
                </article>

                @if ($scoutUrl)
                    <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4">{{ __('Scout uniform photo') }}</h2>
                        <img src="{{ $scoutUrl }}" alt="{{ __('Scout uniform photo') }}"
                            class="w-full max-w-md h-72 object-cover rounded-xl border border-slate-200 dark:border-slate-700">
                    </article>
                @endif
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
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('School graduation year') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->SchoolGraduationYear ?? null) }}</div>
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
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('University graduation year') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->ActualFacultyGraduationYear ?? null) }}</div>
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

            {{-- Scout --}}
            <div x-show="tab === 'scout'" x-cloak class="fade-in">
                <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-5">{{ __('Scout information') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Scout sector') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->QetaaName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Scout rank') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->RotbaName ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Progress badge certificate') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($badgeName) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Joining year') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $val($p->ScoutJoiningYear ?? null) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">{{ __('Shamandora code') }}</div>
                            <div class="font-bold text-slate-900 dark:text-slate-100 font-mono" dir="ltr">{{ $val($code) }}</div>
                        </div>
                    </div>
                </article>
            </div>

            {{-- Questions --}}
            <div x-show="tab === 'questions'" x-cloak class="fade-in">
                <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-2">{{ __('Sector questions') }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">{{ __('Sector:') }} {{ $val($p->QetaaName ?? null) }}</p>
                    @if ($questions->isNotEmpty())
                        <div class="space-y-4">
                            @foreach ($questions as $question)
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4">
                                    <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">{{ $question->QuestionText }}</div>
                                    <div class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-wrap">{{ $question->Answer }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-5 text-amber-900 dark:text-amber-100 text-sm">
                            {{ __('No questions for this person in this sector') }}
                        </div>
                    @endif
                </article>
            </div>

            {{-- Seasons --}}
            <div id="season-panel" x-show="tab === 'seasons'" x-cloak class="fade-in space-y-5">
                <article class="rounded-2xl bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ __('Season activity') }}</h2>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Attendance, absences, exam scores and event finance for the selected season.') }}</p>
                        </div>
                        <form method="GET" action="{{ route('person.show', $p->PersonID) }}" class="flex items-center gap-2">
                            <input type="hidden" name="tab" value="seasons">
                            <label for="season" class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Season') }}</label>
                            <select id="season" name="season" onchange="this.form.submit()"
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

                @if (! $selectedSeasonId)
                    <div class="rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-5 text-amber-900 dark:text-amber-100 text-sm">
                        {{ __('No active season selected.') }}
                    </div>
                @else
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
                                    @if (!empty($ev->Excuse))
                                        <div class="text-xs text-amber-700 dark:text-amber-300 mt-1">{{ __('Excuse') }}: {{ $ev->Excuse }}</div>
                                    @endif
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
                            <div class="px-5 py-3.5 border-b border-slate-50 dark:border-slate-700 last:border-0">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ $exam->ExamDate ?? '—' }}</div>
                                    <div class="text-sm font-bold text-teal-700 dark:text-teal-300">{{ __('Total') }}: {{ $exam->TotalMark }}</div>
                                </div>
                                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                    <div class="rounded-lg bg-slate-50 dark:bg-slate-800 px-3 py-2">
                                        <div class="text-slate-500 dark:text-slate-400">{{ __('Theoretical') }}</div>
                                        <div class="font-bold text-slate-900 dark:text-slate-100">{{ $exam->TheoreticalMark }}</div>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 dark:bg-slate-800 px-3 py-2">
                                        <div class="text-slate-500 dark:text-slate-400">{{ __('Practical') }}</div>
                                        <div class="font-bold text-slate-900 dark:text-slate-100">{{ $exam->PracticalMark }}</div>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 dark:bg-slate-800 px-3 py-2">
                                        <div class="text-slate-500 dark:text-slate-400">{{ __('Sector') }}</div>
                                        <div class="font-bold text-slate-900 dark:text-slate-100 truncate">{{ $exam->QetaaName ?? '—' }}</div>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 dark:bg-slate-800 px-3 py-2">
                                        <div class="text-slate-500 dark:text-slate-400">{{ __('Stage / year') }}</div>
                                        <div class="font-bold text-slate-900 dark:text-slate-100 truncate">{{ $exam->SanaMarhalaName ?? '—' }}</div>
                                    </div>
                                </div>
                                @if (!empty($exam->Note))
                                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $exam->Note }}</div>
                                @endif
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
                                <div class="text-xs sm:text-sm text-end space-y-0.5">
                                    <div class="font-semibold text-slate-800 dark:text-slate-100">{{ __('Required') }}: {{ $finance->FinalRequiredAmount }}</div>
                                    <div class="text-emerald-700 dark:text-emerald-300">{{ __('Paid') }}: {{ $finance->AmountPaid }}</div>
                                    <div class="text-rose-700 dark:text-rose-300">{{ __('Remaining') }}: {{ $finance->RemainingAmount }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-slate-500 dark:text-slate-400 text-sm">{{ __('No event finance records for this season.') }}</div>
                        @endforelse
                    </article>
                @endif
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
                <div class="px-5 pb-5 grid grid-cols-2 gap-3 text-center text-xs">
                    <div class="rounded-xl bg-white/10 px-2 py-2.5">
                        <div class="text-teal-100/80">{{ __('Joining year') }}</div>
                        <div class="font-bold mt-0.5">{{ $val($p->ScoutJoiningYear ?? null) }}</div>
                    </div>
                    <div class="rounded-xl bg-white/10 px-2 py-2.5">
                        <div class="text-teal-100/80">{{ __('Sector') }}</div>
                        <div class="font-bold mt-0.5 truncate">{{ $val($p->QetaaName ?? null) }}</div>
                    </div>
                    <div class="rounded-xl bg-white/10 px-2 py-2.5 col-span-2">
                        <div class="text-teal-100/80">{{ __('Stage / year') }}</div>
                        <div class="font-bold mt-0.5 truncate">{{ $val($p->SanaMarhalaName ?? null) }}</div>
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

            <button type="button" @click="tab = 'seasons'"
                class="w-full rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-3 text-sm transition">
                {{ __('View seasons activity') }}
            </button>
        </aside>
    </div>
</div>
@endsection
