@extends('layouts.app', ['pageTitle' => __('View applicant data')])

@section('content')
    <style>
        .person-form-page input[type="text"],
        .person-form-page input[type="email"],
        .person-form-page input[type="date"],
        .person-form-page input[type="number"],
        .person-form-page input[type="url"],
        .person-form-page select,
        .person-form-page textarea {
            min-height: 50px;
        }

        .person-form-page textarea {
            height: auto;
        }
    </style>

    <div class="person-form-page py-8">
        @if (session('status'))
            <div class="max-w-6xl mx-auto px-4 mb-4">
                <div class="rounded-xl bg-emerald-600 text-white px-5 py-4 shadow">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <div class="max-w-6xl mx-auto px-4">
            <div class="rounded-3xl bg-white dark:bg-slate-900 shadow-xl ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden">

                <div class="px-6 md:px-10 py-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <div class="flex flex-col items-center justify-center gap-4 text-center">
                        <img src="{{ asset('img/shamandora.png') }}" alt="Logo" class="h-20 w-20 object-contain dark:hidden" />
                        <img src="{{ asset('img/shamandora-dark.png') }}" alt="Logo" class="h-20 w-20 object-contain hidden dark:block" />
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __('Applicant data') }}</h1>
                            <p class="text-slate-500 dark:text-slate-400 mt-2">{{ __('View all registered data in the same style as new enrolments pages') }}</p>
                        </div>
                    </div>
                </div>

                @php
                    $buildImgUrl = function ($path) {
                        if (!$path) {
                            return null;
                        }

                        $p = trim($path);

                        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
                            return $p;
                        }

                        if (
                            str_starts_with($p, 'storage/') ||
                            str_starts_with($p, 'uploads/') ||
                            str_starts_with($p, 'img/')
                        ) {
                            return asset($p);
                        }

                        return asset('storage/' . ltrim($p, '/'));
                    };

                    $personalUrl = $buildImgUrl($person->PersonalImagePath ?? null);
                    $scoutUrl = $buildImgUrl($person->ScoutImagePath ?? null);
                @endphp

                <div class="p-6 md:p-10 space-y-8">

                    @if ($personalUrl || $scoutUrl)
                        <div class="mb-2">
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="font-bold text-slate-800 dark:text-slate-100">{{ __('Photos') }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ __('Only available photos will be shown') }}</div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    @if ($personalUrl)
                                        <div class="md:col-span-6">
                                            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                                                <div class="font-semibold text-slate-800 dark:text-slate-100 mb-3">{{ __('Personal photo') }}</div>
                                                <img src="{{ $personalUrl }}" alt="{{ __('Personal photo') }}"
                                                    class="w-full h-80 object-cover rounded-xl border border-slate-200 dark:border-slate-700">
                                            </div>
                                        </div>
                                    @endif

                                    @if ($scoutUrl)
                                        <div class="md:col-span-6">
                                            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                                                <div class="font-semibold text-slate-800 dark:text-slate-100 mb-3">{{ __('Scout uniform photo') }}</div>
                                                <img src="{{ $scoutUrl }}" alt="{{ __('Scout uniform photo') }}"
                                                    class="w-full h-80 object-cover rounded-xl border border-slate-200 dark:border-slate-700">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 1: Personal information') }}</h2>
                                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __("Show the applicant's basic information.") }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">1
                                / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('ID number') }}</label>
                                <input type="text" readonly value="{{ $person->PersonID }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Shamandora code') }}</label>
                                <input type="text" readonly value="{{ $person->ShamandoraCode ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Applicant gender') }}</label>
                                <input type="text" readonly value="{{ $person->Gender == 'Male' ? __('Male') : __('Female') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Blood type') }}</label>
                                <input type="text" readonly value="{{ $person->BloodTypeName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('First name') }}</label>
                                <input type="text" readonly value="{{ $person->FirstName }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Second name') }}</label>
                                <input type="text" readonly value="{{ $person->SecondName }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Third name') }}</label>
                                <input type="text" readonly value="{{ $person->ThirdName }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Fourth name') }}</label>
                                <input type="text" readonly value="{{ $person->FourthName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Email') }}</label>
                                <input type="text" readonly value="{{ $person->PersonalEmail ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Date of birth') }}</label>
                                <input type="text" readonly value="{{ $person->DateOfBirth ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Joining year') }}</label>
                                <input type="text" readonly value="{{ $person->ScoutJoiningYear ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('National ID') }}</label>
                                <input type="text" readonly value="{{ $person->RaqamQawmy ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Facebook link') }}</label>
                                <input type="text" readonly value="{{ $person->FacebookProfileURL ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Instagram link') }}</label>
                                <input type="text" readonly value="{{ $person->InstagramProfileURL ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none"
                                    dir="ltr">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 2: Contact and address') }}</h2>
                                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Show contact numbers and address.') }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">2
                                / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Personal mobile') }}</label>
                                <input type="text" readonly
                                    value="{{ $person->PersonPersonalMobileNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Father mobile') }}</label>
                                <input type="text" readonly value="{{ $person->FatherMobileNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Mother mobile') }}</label>
                                <input type="text" readonly value="{{ $person->MotherMobileNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Landline') }}</label>
                                <input type="text" readonly value="{{ $person->HomePhoneNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('WhatsApp on primary number') }}</label>
                                <input type="text" readonly
                                    value="{{ (string) ($person->IsOPersonalPhoneNumberHavingWhatsapp ?? '') === '1' ? __('Yes') : __('No') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Building number') }}</label>
                                <input type="text" readonly value="{{ $person->BuildingNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Floor number') }}</label>
                                <input type="text" readonly value="{{ $person->FloorNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Apartment number') }}</label>
                                <input type="text" readonly value="{{ $person->AppartmentNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Main street') }}</label>
                                <input type="text" readonly value="{{ $person->MainStreetName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Side street') }}</label>
                                <input type="text" readonly value="{{ $person->SubStreetName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Nearest landmark') }}</label>
                                <input type="text" readonly value="{{ $person->NearestLandmark ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Area') }}</label>
                                <input type="text" readonly value="{{ $person->ManteqaName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('District') }}</label>
                                <input type="text" readonly value="{{ $person->DistrictName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 3: Educational and church data') }}</h2>
                                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Show educational and church data.') }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">3
                                / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Academic year and stage') }}</label>
                                <input type="text" readonly value="{{ $person->SanaMarhalaName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Job name') }}</label>
                                <input type="text" readonly value="{{ $person->JobName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Workplace') }}</label>
                                <input type="text" readonly value="{{ $person->WorkPlace ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('School name') }}</label>
                                <input type="text" readonly value="{{ $person->SchoolName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('School graduation year') }}</label>
                                <input type="text" readonly value="{{ $person->SchoolGraduationYear ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Faculty') }}</label>
                                <input type="text" readonly value="{{ $person->FacultyName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('University') }}</label>
                                <input type="text" readonly value="{{ $person->UniversityName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('University graduation year') }}</label>
                                <input type="text" readonly
                                    value="{{ $person->ActualFacultyGraduationYear ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Spiritual father name') }}</label>
                                <input type="text" readonly value="{{ $person->SpiritualFatherName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __("Spiritual father's church / confession father's church") }}</label>
                                <input type="text" readonly
                                    value="{{ $person->SpiritualFatherChurchName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 4: Scout information') }}</h2>
                                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Sector, rank and certificate details.') }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">4
                                / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Scout rank') }}</label>
                                <input type="text" readonly value="{{ $person->RotbaName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Progress badge certificate') }}</label>
                                <input type="text" readonly
                                    value="{{ $person->EgazetBetakatTaqaddomName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Scout sector') }}</label>
                                <input type="text" readonly value="{{ $person->QetaaName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Final part: Sector questions') }}</h2>
                                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Show questions and their recorded answers clearly.') }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">5
                                / 5</span>
                        </div>

                        <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4 mb-4 text-slate-800 dark:text-slate-100">
                            <div class="font-bold">{{ __('Sector:') }} {{ $person->QetaaName ?? __('None') }}</div>
                        </div>

                        @if (!$questions->isEmpty())
                            <div class="space-y-4">
                                @foreach ($questions as $question)
                                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                                        <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">
                                            {{ __('Question:') }} {{ $question->QuestionText }}
                                        </div>
                                        <div class="text-sm text-slate-600 dark:text-slate-300 mb-2">{{ __('Applicant answer') }}</div>
                                        <textarea readonly rows="3"
                                            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">{{ $question->Answer }}</textarea>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-5 text-amber-900 dark:text-amber-100">
                                {{ __('No questions for this person in this sector') }}
                            </div>
                        @endif
                    </section>

                </div>
            </div>
        </div>
    </div>
@endsection
