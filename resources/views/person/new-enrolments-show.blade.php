@extends('layouts.app', ['pageTitle' => __('View enrolment request')])

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

        @if (session('success'))
            <div class="max-w-6xl mx-auto px-4 mb-4">
                <div class="rounded-xl bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-200 px-5 py-4">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-6xl mx-auto px-4 mb-4">
                <div class="rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-200 px-5 py-4">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <div class="max-w-6xl mx-auto px-4">
            <div class="rounded-3xl bg-white dark:bg-slate-900 shadow-xl ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden">

                <div class="px-6 md:px-10 py-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <div class="flex flex-col items-center justify-center gap-4 text-center">
                        <img src="{{ asset('img/shamandora.webp') }}" alt="Logo" class="h-14 w-14 object-contain dark:hidden" />
                        <img src="{{ asset('img/shamandora-dark.webp') }}" alt="Logo" class="h-14 w-14 object-contain hidden dark:block" />
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __('Applicant data') }}</h1>
                            <p class="text-slate-500 dark:text-slate-400 mt-2">{{ __('View all registered enrolment data') }}</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 md:px-10 py-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ __('Status:') }}</span>
                            @if ((int) ($person->IsApproved ?? 0) === 1)
                                <span class="px-3 py-1 rounded-full text-xs bg-green-50 text-green-700 border border-green-200 dark:bg-green-950/40 dark:text-green-300 dark:border-green-800">{{ __('Approved') }}</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs bg-yellow-50 text-yellow-700 border border-yellow-200 dark:bg-yellow-950/40 dark:text-yellow-300 dark:border-yellow-800">{{ __('Pending review') }}</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('person.new-enrolments-index') }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 transition-colors">
                                {{ __('Back to list') }}
                            </a>
                            <a href="{{ route('person.new-enrolments-edit', $person->PersonID) }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-yellow-500 hover:bg-yellow-600 transition-colors">
                                {{ __('Edit') }}
                            </a>
                            @if ((int) ($person->IsApproved ?? 0) !== 1)
                                <form method="POST" action="{{ route('person.new-enrolments-approve', $person->PersonID) }}">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors">
                                        {{ __('Approve') }}
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('person.new-enrolments-delete', $person->PersonID) }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 transition-colors">
                                {{ __('Reject') }}
                            </a>
                        </div>
                    </div>
                </div>

                @php
                    $personalPath = $person->PersonalImagePath ?? null;
                    $scoutPath = $person->ScoutImagePath ?? null;

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

                    $personalUrl = $buildImgUrl($personalPath);
                    $scoutUrl = $buildImgUrl($scoutPath);

                    $allergyFood = trim((string) ($person->AllergyFood ?? ($person->allergy_food ?? '')));
                    $allergyMedicine = trim((string) ($person->AllergyMedicine ?? ($person->allergy_medicine ?? '')));
                    $medicalDiseases = trim((string) ($person->MedicalDiseases ?? ($person->medical_diseases ?? '')));
                    $medicalMedications = trim((string) ($person->MedicalMedications ?? ($person->medical_medications ?? '')));
                    $hasEmergency = $person->HasEmergencyCase ?? ($person->has_emergency_case ?? null);
                    $emergencyDetailsVal = trim((string) ($person->EmergencyDetails ?? ($person->emergency_details ?? '')));

                    $hasAllergy = $allergyFood !== '' || $allergyMedicine !== '';
                    $hasMedical =
                        $medicalDiseases !== '' ||
                        $medicalMedications !== '' ||
                        $hasEmergency == 1 ||
                        $hasEmergency === true ||
                        $hasEmergency === '1';

                    $yesNo = function ($v) {
                        return $v == 1 || $v === true || $v === '1' || $v === 'True' ? __('Yes') : __('No');
                    };
                @endphp

                <div class="p-6 md:p-10 space-y-8">

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 1: Personal information') }}</h2>
                                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __("Show the applicant's basic information.") }}</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">1 / 5</span>
                        </div>

                        @if ($personalUrl || $scoutUrl)
                            <div class="mb-6">
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

                        @if ($hasAllergy)
                            <div class="mb-6">
                                <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <div class="font-bold text-slate-800 dark:text-slate-100">{{ __('Allergy section') }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ __('Only shown when data exists') }}</div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        @if ($allergyFood !== '')
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Food allergy') }}</label>
                                                <input type="text" readonly value="{{ $allergyFood }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                            </div>
                                        @endif

                                        @if ($allergyMedicine !== '')
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Medicine allergy') }}</label>
                                                <input type="text" readonly value="{{ $allergyMedicine }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($hasMedical)
                            <div class="mb-6">
                                <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <div class="font-bold text-slate-800 dark:text-slate-100">{{ __('Medical history section') }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ __('Only shown when data exists') }}</div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        @if ($medicalDiseases !== '')
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Chronic diseases / diagnosis (if any)') }}</label>
                                                <input type="text" readonly value="{{ $medicalDiseases }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                            </div>
                                        @endif

                                        @if ($medicalMedications !== '')
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Current medications (if any)') }}</label>
                                                <input type="text" readonly value="{{ $medicalMedications }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                            </div>
                                        @endif

                                        @if ($hasEmergency !== null)
                                            <div class="md:col-span-12">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Previous emergency cases?') }}</label>
                                                <input type="text" readonly value="{{ $yesNo($hasEmergency) }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                            </div>
                                        @endif

                                        @if ($emergencyDetailsVal !== '')
                                            <div class="md:col-span-12">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Emergency details') }}</label>
                                                <input type="text" readonly value="{{ $emergencyDetailsVal }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Request sequence') }}</label>
                                <input type="text" readonly value="{{ $person->PersonID }}"
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
                                <input type="text" readonly value="{{ $person->FourthName ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none"
                                    placeholder="{{ __('None') }}">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Applicant gender') }}</label>
                                <input type="text" readonly value="{{ $person->Gender == 'Male' ? __('Male') : __('Female') }}"
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
                                <input type="text" readonly value="{{ $person->DateOfBirth }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Joining year') }}</label>
                                <input type="text" readonly value="{{ $person->ScoutJoiningYear ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('National ID') }}</label>
                                <input type="text" readonly value="{{ $person->RaqamQawmy }}"
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

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Blood type') }}</label>
                                <input type="text" readonly value="{{ $person->BloodTypeName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 2: Contact and address') }}</h2>
                                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Show contact numbers and address.') }}</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">2 / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Personal mobile') }}</label>
                                <input type="text" readonly value="{{ $person->PersonPersonalMobileNumber }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Father mobile') }}</label>
                                <input type="text" readonly value="{{ $person->FatherMobileNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Mother mobile') }}</label>
                                <input type="text" readonly value="{{ $person->MotherMobileNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Landline') }}</label>
                                <input type="text" readonly value="{{ $person->HomePhoneNumber ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('WhatsApp on primary number') }}</label>
                                <input type="text" readonly
                                    value="{{ $person->IsOPersonalPhoneNumberHavingWhatsapp == true || $person->IsOPersonalPhoneNumberHavingWhatsapp == 'True' || $person->IsOPersonalPhoneNumberHavingWhatsapp == 1 ? __('Yes') : __('No') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-12 mt-2">
                                <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4">
                                    <div class="font-bold text-slate-800 dark:text-slate-100 mb-3">{{ __('Address') }}</div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Building number') }}</label>
                                            <input type="text" readonly value="{{ $person->BuildingNumber ?? __('None') }}"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                        </div>

                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Floor number') }}</label>
                                            <input type="text" readonly value="{{ $person->FloorNumber ?? __('None') }}"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                        </div>

                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Apartment number') }}</label>
                                            <input type="text" readonly value="{{ $person->AppartmentNumber ?? __('None') }}"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Side street') }}</label>
                                            <input type="text" readonly value="{{ $person->SubStreetName ?? __('None') }}"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Main street') }}</label>
                                            <input type="text" readonly value="{{ $person->MainStreetName ?? __('None') }}"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                        </div>

                                        <div class="md:col-span-12">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Nearest landmark') }}</label>
                                            <input type="text" readonly value="{{ $person->NearestLandmark ?? __('None') }}"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none"
                                                placeholder="{{ __('None') }}">
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Area') }}</label>
                                            <input type="text" readonly value="{{ $person->ManteqaName ?? __('None') }}"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('District') }}</label>
                                            <input type="text" readonly value="{{ $person->DistrictName ?? __('None') }}"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 3: Educational and church data') }}</h2>
                                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Show educational and church data.') }}</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">3 / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Academic year and stage') }}</label>
                                <input type="text" readonly value="{{ $person->SanaMarhalaName ?? __('None') }}"
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
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Spiritual father name') }}</label>
                                <input type="text" readonly value="{{ $person->SpiritualFatherName ?? __('None') }}"
                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Spiritual father church') }}</label>
                                <input type="text" readonly value="{{ $person->SpiritualFatherChurchName ?? __('None') }}"
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
                            <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">4 / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-12">
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
                            <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">5 / 5</span>
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
                                        <input type="text" readonly value="{{ $question->Answer }}"
                                            class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
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
