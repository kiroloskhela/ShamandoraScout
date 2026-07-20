@extends('layouts.app', ['pageTitle' => __('Edit enrolment request')])

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

        @if ($errors->any())
            <div class="max-w-6xl mx-auto px-4 mb-4">
                <div class="rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-200 px-5 py-4">
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('person.new-enrolments-update', $person->PersonID) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="max-w-6xl mx-auto px-4">
                <div class="rounded-3xl bg-white dark:bg-slate-900 shadow-xl ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden">

                    <div class="px-6 md:px-10 py-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                        <div class="flex flex-col items-center justify-center gap-4 text-center">
                            <img src="{{ asset('img/shamandora.webp') }}" alt="Logo" class="h-14 w-14 object-contain dark:hidden" />
                            <img src="{{ asset('img/shamandora-dark.webp') }}" alt="Logo" class="h-14 w-14 object-contain hidden dark:block" />
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">{{ __('Edit enrollee data') }}</h1>
                                <p class="text-slate-500 dark:text-slate-400 mt-2">{{ __('Edit all registered enrolment data') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 md:px-10 py-4 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                        <div class="flex items-center justify-end flex-wrap gap-2">
                            <a href="{{ route('person.new-enrolments-index') }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 transition-colors">
                                {{ __('Back to list') }}
                            </a>
                            <a href="{{ route('person.new-enrolments-show', $person->PersonID) }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                {{ __('View') }}
                            </a>
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
                    @endphp

                    <div class="p-6 md:p-10 space-y-8">

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 1: Personal information') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __("Edit the applicant's basic information.") }}</p>
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
                                                    <input type="text" name="allergy_food" value="{{ $allergyFood }}"
                                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                            @endif

                                            @if ($allergyMedicine !== '')
                                                <div class="md:col-span-6">
                                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Medicine allergy') }}</label>
                                                    <input type="text" name="allergy_medicine" value="{{ $allergyMedicine }}"
                                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                                                    <input type="text" name="medical_diseases" value="{{ $medicalDiseases }}"
                                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                            @endif

                                            @if ($medicalMedications !== '')
                                                <div class="md:col-span-6">
                                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Current medications (if any)') }}</label>
                                                    <input type="text" name="medical_medications" value="{{ $medicalMedications }}"
                                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                            @endif

                                            @if ($hasEmergency !== null)
                                                <div class="md:col-span-12">
                                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Previous emergency cases?') }}</label>
                                                    <select name="has_emergency_case"
                                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option value="0" {{ $hasEmergency == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                                                        <option value="1" {{ $hasEmergency == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                                    </select>
                                                </div>
                                            @endif

                                            @if ($emergencyDetailsVal !== '')
                                                <div class="md:col-span-12">
                                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Emergency details') }}</label>
                                                    <textarea name="emergency_details" rows="3"
                                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $emergencyDetailsVal }}</textarea>
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
                                    <input type="text" name="first_name" value="{{ old('first_name', $person->FirstName) }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Second name') }}</label>
                                    <input type="text" name="second_name" value="{{ old('second_name', $person->SecondName) }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Third name') }}</label>
                                    <input type="text" name="third_name" value="{{ old('third_name', $person->ThirdName) }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Fourth name') }}</label>
                                    <input type="text" name="fourth_name" value="{{ old('fourth_name', $person->FourthName ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Applicant gender') }}</label>
                                    <select name="gender"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="Male" {{ old('gender', $person->Gender) == 'Male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                        <option value="Female" {{ old('gender', $person->Gender) == 'Female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Email') }}</label>
                                    <input type="email" name="personal_email" value="{{ old('personal_email', $person->PersonalEmail ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Date of birth') }}</label>
                                    <input type="date" name="birthdate_input" value="{{ old('birthdate_input', $person->DateOfBirth) }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Joining year') }}</label>
                                    <input type="number" name="joining_year_input" value="{{ old('joining_year_input', $person->ScoutJoiningYear ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('National ID') }}</label>
                                    <input type="text" name="input_raqam_qawmy" value="{{ old('input_raqam_qawmy', $person->RaqamQawmy) }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr" required minlength="14" maxlength="14">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Facebook link') }}</label>
                                    <input type="url" name="facebook_profile_url" value="{{ old('facebook_profile_url', $person->FacebookProfileURL ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Instagram link') }}</label>
                                    <input type="url" name="instagram_profile_url" value="{{ old('instagram_profile_url', $person->InstagramProfileURL ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Blood type') }}</label>
                                    <select name="blood_type_input"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">{{ __('Choose blood type') }}</option>
                                        @foreach ($blood as $b)
                                            <option value="{{ $b->BloodTypeID }}"
                                                {{ (string) old('blood_type_input', $person->BloodTypeID) === (string) $b->BloodTypeID ? 'selected' : '' }}>
                                                {{ $b->BloodTypeName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 2: Contact and address') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Edit contact numbers and address.') }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">2 / 5</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Personal mobile') }}</label>
                                    <input type="text" name="personal_phone_number" value="{{ old('personal_phone_number', $person->PersonPersonalMobileNumber) }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr" required minlength="11" maxlength="11">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Father mobile') }}</label>
                                    <input type="text" name="father_mobile_number" value="{{ old('father_mobile_number', $person->FatherMobileNumber ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Mother mobile') }}</label>
                                    <input type="text" name="mother_mobile_number" value="{{ old('mother_mobile_number', $person->MotherMobileNumber ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Landline') }}</label>
                                    <input type="text" name="home_phone_number" value="{{ old('home_phone_number', $person->HomePhoneNumber ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr">
                                </div>

                                <div class="md:col-span-12">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('WhatsApp on primary number') }}</label>
                                    <select name="is_personal_phone_has_whatsapp"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="0" {{ (string) old('is_personal_phone_has_whatsapp', $person->IsOPersonalPhoneNumberHavingWhatsapp) === '0' ? 'selected' : '' }}>{{ __('No') }}</option>
                                        <option value="1" {{ (string) old('is_personal_phone_has_whatsapp', $person->IsOPersonalPhoneNumberHavingWhatsapp) === '1' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                    </select>
                                </div>

                                <div class="md:col-span-12 mt-2">
                                    <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4">
                                        <div class="font-bold text-slate-800 dark:text-slate-100 mb-3">{{ __('Address') }}</div>

                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                            <div class="md:col-span-4">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Building number') }}</label>
                                                <input type="text" name="building_number" value="{{ old('building_number', $person->BuildingNumber ?? '') }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>

                                            <div class="md:col-span-4">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Floor number') }}</label>
                                                <input type="text" name="floor_number" value="{{ old('floor_number', $person->FloorNumber ?? '') }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>

                                            <div class="md:col-span-4">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Apartment number') }}</label>
                                                <input type="text" name="appartment_number" value="{{ old('appartment_number', $person->AppartmentNumber ?? '') }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>

                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Side street') }}</label>
                                                <input type="text" name="sub_street_name" value="{{ old('sub_street_name', $person->SubStreetName ?? '') }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>

                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Main street') }}</label>
                                                <input type="text" name="main_street_name" value="{{ old('main_street_name', $person->MainStreetName ?? '') }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>

                                            <div class="md:col-span-12">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Nearest landmark') }}</label>
                                                <input type="text" name="nearest_landmark" value="{{ old('nearest_landmark', $person->NearestLandmark ?? '') }}"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>

                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Area') }}</label>
                                                <select name="manteqa_id"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="">{{ __('Choose area') }}</option>
                                                    @foreach ($manateq as $m)
                                                        <option value="{{ $m->ManteqaID }}"
                                                            {{ (string) old('manteqa_id', $person->ManteqaID) === (string) $m->ManteqaID ? 'selected' : '' }}>
                                                            {{ $m->ManteqaName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('District') }}</label>
                                                <select name="district_id"
                                                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="">{{ __('Choose district') }}</option>
                                                    @foreach ($districts as $d)
                                                        <option value="{{ $d->DistrictID }}"
                                                            {{ (string) old('district_id', $person->DistrictID) === (string) $d->DistrictID ? 'selected' : '' }}>
                                                            {{ $d->DistrictName }}
                                                        </option>
                                                    @endforeach
                                                </select>
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
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Edit educational and church data if available.') }}</p>
                                </div>
                                <span class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">3 / 5</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-12">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Academic year and stage') }}</label>
                                    <select name="sana_marhala_id"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">{{ __('Choose year and stage') }}</option>
                                        @foreach ($seneen_marahel as $sm)
                                            <option value="{{ $sm->SanaMarhalaID }}"
                                                {{ (string) old('sana_marhala_id', $person->SanaMarhalaID) === (string) $sm->SanaMarhalaID ? 'selected' : '' }}>
                                                {{ $sm->SanaMarhalaName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('School name') }}</label>
                                    <input type="text" name="school_name" value="{{ old('school_name', $person->SchoolName ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('School graduation year') }}</label>
                                    <input type="number" name="school_graduation_year" value="{{ old('school_graduation_year', $person->SchoolGraduationYear ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Spiritual father name') }}</label>
                                    <input type="text" name="spiritual_father_name" value="{{ old('spiritual_father_name', $person->SpiritualFatherName ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Spiritual father church') }}</label>
                                    <input type="text" name="spiritual_father_church_name" value="{{ old('spiritual_father_church_name', $person->SpiritualFatherChurchName ?? '') }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 4: Scout information') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Edit sector.') }}</p>
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
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">{{ __('Edit recorded answers.') }}</p>
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
                                            <textarea name="question_{{ $question->QuestionID }}" rows="3"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('question_' . $question->QuestionID, $question->Answer) }}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-5 text-amber-900 dark:text-amber-100">
                                    {{ __('No questions for this person in this sector') }}
                                </div>
                            @endif
                        </section>

                        <div class="flex justify-center mt-8">
                            <button type="submit"
                                class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition duration-200">
                                {{ __('Save changes') }}
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
