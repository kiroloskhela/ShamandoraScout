@extends('layouts.app', ['pageTitle' => 'تعديل بيانات الملتحق'])

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

        <form method="POST" action="{{ route('person.update', $person->PersonID) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <input type="hidden" name="RequestPersonID" value="{{ auth()->user()->PersonID ?? '' }}">

            <div class="max-w-6xl mx-auto px-4">
                <div class="rounded-3xl bg-white dark:bg-slate-900 shadow-xl ring-1 ring-slate-200 dark:ring-slate-700 overflow-hidden">

                    <div class="px-6 md:px-10 py-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                        <div class="flex flex-col items-center justify-center gap-4 text-center">
                            <img src="{{ asset('img/shamandora.png') }}" alt="Logo" class="h-20 w-20 object-contain dark:hidden" />
                            <img src="{{ asset('img/shamandora-dark.png') }}" alt="Logo" class="h-20 w-20 object-contain hidden dark:block" />
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold text-slate-900 dark:text-slate-100">تعديل بيانات الملتحق</h1>
                                <p class="text-slate-500 dark:text-slate-400 mt-2">تعديل جميع البيانات المسجلة بنفس أسلوب صفحات الملتحقين الجدد
                                </p>
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

                        <div class="mb-2">
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="font-bold text-slate-800 dark:text-slate-100">{{ __('Photos') }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">يمكنك عرض الصور الحالية وتغييرها</div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <div class="md:col-span-6">
                                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                                            <div class="font-semibold text-slate-800 dark:text-slate-100 mb-3">{{ __('Personal photo') }}</div>

                                            @if ($personalUrl)
                                                <img src="{{ $personalUrl }}" alt="{{ __('Personal photo') }}"
                                                    class="w-full h-72 object-cover rounded-xl border border-slate-200 dark:border-slate-700 mb-4">
                                            @else
                                                <div
                                                    class="w-full h-72 rounded-xl border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-center text-slate-400 mb-4">
                                                    لا توجد صورة شخصية
                                                </div>
                                            @endif

                                            <input type="file" name="personal_image" accept="image/*"
                                                class="block w-full text-sm text-slate-700 dark:text-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-white hover:file:bg-blue-700">
                                        </div>
                                    </div>

                                    <div class="md:col-span-6">
                                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                                            <div class="font-semibold text-slate-800 dark:text-slate-100 mb-3">{{ __('Scout uniform photo') }}</div>

                                            @if ($scoutUrl)
                                                <img src="{{ $scoutUrl }}" alt="{{ __('Scout uniform photo') }}"
                                                    class="w-full h-72 object-cover rounded-xl border border-slate-200 dark:border-slate-700 mb-4">
                                            @else
                                                <div
                                                    class="w-full h-72 rounded-xl border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-center text-slate-400 mb-4">
                                                    لا توجد صورة كشفية
                                                </div>
                                            @endif

                                            <input type="file" name="scout_image" accept="image/*"
                                                class="block w-full text-sm text-slate-700 dark:text-slate-200 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-white hover:file:bg-blue-700">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 1: Personal information') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">تعديل البيانات الأساسية للملتحق.</p>
                                </div>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">1
                                    / 5</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الرقم التعريفي</label>
                                    <input type="text" readonly value="{{ $person->PersonID }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Shamandora code') }}</label>
                                    <input type="text" readonly value="{{ $person->ShamandoraCode ?? 'لا يوجد' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('First name') }}</label>
                                    <input type="text" name="first_name" value="{{ $person->FirstName }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Second name') }}</label>
                                    <input type="text" name="second_name" value="{{ $person->SecondName }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Third name') }}</label>
                                    <input type="text" name="third_name" value="{{ $person->ThirdName }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Fourth name') }}</label>
                                    <input type="text" name="fourth_name" value="{{ $person->FourthName ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Applicant gender') }}</label>
                                    <select name="gender"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                        <option value="Male" {{ $person->Gender == 'Male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                        <option value="Female" {{ $person->Gender == 'Female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">البريد
                                        الإلكتروني</label>
                                    <input type="email" name="email_input" value="{{ $person->PersonalEmail ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Date of birth') }}</label>
                                    <input type="date" name="birthdate_input" value="{{ $person->DateOfBirth }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Joining year') }}</label>
                                    <input type="number" name="joining_year_input"
                                        value="{{ $person->ScoutJoiningYear }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('National ID') }}</label>
                                    <input type="number" name="input_raqam_qawmy" value="{{ $person->RaqamQawmy }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Blood type') }}</label>
                                    <select name="blood_type_input"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">{{ __('Choose blood type') }}</option>
                                        @foreach ($blood as $b)
                                            <option value="{{ $b->BloodTypeID }}"
                                                {{ $person->BloodTypeID == $b->BloodTypeID ? 'selected' : '' }}>
                                                {{ $b->BloodTypeName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">لينك فيسبوك</label>
                                    <input type="url" name="inputFacebookLink"
                                        value="{{ $person->FacebookProfileURL ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr">
                                </div>

                                <div class="md:col-span-12">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">لينك انستجرام</label>
                                    <input type="url" name="inputInstagramLink"
                                        value="{{ $person->InstagramProfileURL ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        dir="ltr">
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">الجزء الثاني: بيانات التواصل والعنوان</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">تعديل أرقام التواصل والعنوان.</p>
                                </div>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">2
                                    / 5</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Personal mobile') }}</label>
                                    <input type="number" name="personal_phone_number"
                                        value="{{ $person->PersonPersonalMobileNumber ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Father mobile') }}</label>
                                    <input type="number" name="father_phone_number"
                                        value="{{ $person->FatherMobileNumber ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Mother mobile') }}</label>
                                    <input type="number" name="mother_phone_number"
                                        value="{{ $person->MotherMobileNumber ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Landline') }}</label>
                                    <input type="text" name="home_phone_number"
                                        value="{{ $person->HomePhoneNumber ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">هل عليه واتساب؟</label>
                                    <select name="has_whatsapp"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="1"
                                            {{ (string) ($person->IsOPersonalPhoneNumberHavingWhatsapp ?? '') === '1' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                        <option value="0"
                                            {{ (string) ($person->IsOPersonalPhoneNumberHavingWhatsapp ?? '') === '0' ? 'selected' : '' }}>{{ __('No') }}</option>
                                    </select>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Building number') }}</label>
                                    <input type="text" name="building_number"
                                        value="{{ $person->BuildingNumber ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Floor number') }}</label>
                                    <input type="text" name="floor_number" value="{{ $person->FloorNumber ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Apartment number') }}</label>
                                    <input type="text" name="appartment_number"
                                        value="{{ $person->AppartmentNumber ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Main street') }}</label>
                                    <input type="text" name="main_street_name"
                                        value="{{ $person->MainStreetName ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Side street') }}</label>
                                    <input type="text" name="sub_street_name"
                                        value="{{ $person->SubStreetName ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required>
                                </div>

                                <div class="md:col-span-12">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Nearest landmark') }}</label>
                                    <input type="text" name="nearest_landmark"
                                        value="{{ $person->NearestLandmark ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Area') }}</label>
                                    <select name="manteqa_id"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">اختر المنطقة</option>
                                        @foreach ($manateq as $m)
                                            <option value="{{ $m->ManteqaID }}"
                                                {{ $person->ManteqaID == $m->ManteqaID ? 'selected' : '' }}>
                                                {{ $m->ManteqaName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('District') }}</label>
                                    <select name="district_id"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">اختر الحي</option>
                                        @foreach ($districts as $d)
                                            <option value="{{ $d->DistrictID }}"
                                                {{ $person->DistrictID == $d->DistrictID ? 'selected' : '' }}>
                                                {{ $d->DistrictName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">الجزء الثالث: البيانات الدراسية والكنسية
                                    </h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">تعديل البيانات التعليمية والكنسية.</p>
                                </div>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">3
                                    / 5</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-12">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">السنة والمرحلة
                                        الدراسية</label>
                                    <select name="sana_marhala_id"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">اختر السنة والمرحلة</option>
                                        @foreach ($seneen_marahel as $sm)
                                            <option value="{{ $sm->SanaMarhalaID }}"
                                                {{ $person->SanaMarhalaID == $sm->SanaMarhalaID ? 'selected' : '' }}>
                                                {{ $sm->SanaMarhalaName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">اسم الوظيفة</label>
                                    <input type="text" name="person_job" value="{{ $person->JobName ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Workplace') }}</label>
                                    <input type="text" name="person_job_place" value="{{ $person->WorkPlace ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('School name') }}</label>
                                    <input type="text" name="school_name" value="{{ $person->SchoolName ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">سنة التخرج من
                                        المدرسة</label>
                                    <input type="text" name="school_grad_year"
                                        value="{{ $person->SchoolGraduationYear ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Faculty') }}</label>
                                    <select name="person_faculty"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">اختر الكلية</option>
                                        @foreach ($faculties as $faculty)
                                            <option value="{{ $faculty->FacultyID }}"
                                                {{ $person->FacultyID == $faculty->FacultyID ? 'selected' : '' }}>
                                                {{ $faculty->FacultyName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('University') }}</label>
                                    <select name="person_university"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">اختر الجامعة</option>
                                        @foreach ($universities as $university)
                                            <option value="{{ $university->UniversityID }}"
                                                {{ $person->UniversityID == $university->UniversityID ? 'selected' : '' }}>
                                                {{ $university->UniversityName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">سنة التخرج من
                                        الجامعة</label>
                                    <input type="text" name="university_grad_year"
                                        value="{{ $person->ActualFacultyGraduationYear ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Spiritual father name') }}</label>
                                    <input type="text" name="spiritual_father"
                                        value="{{ $person->SpiritualFatherName ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-12">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">كنيسة الأب الروحي / أب
                                        الاعتراف</label>
                                    <input type="text" name="spiritual_father_church"
                                        value="{{ $person->SpiritualFatherChurchName ?? '' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Part 4: Scout information') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">تعديل بيانات الرتبة والبطاقة والقطاع.</p>
                                </div>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">4
                                    / 5</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Scout rank') }}</label>
                                    <select name="rotba_kashfeyya_id"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">اختر الرتبة</option>
                                        @foreach ($rotab as $rotba)
                                            <option value="{{ $rotba->RotbaID }}"
                                                {{ $person->RotbaID == $rotba->RotbaID ? 'selected' : '' }}>
                                                {{ $rotba->RotbaName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">إجازة بطاقة
                                        التقدم</label>
                                    <select name="betaka_id"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">اختر الإجازة</option>
                                        @foreach ($betakat as $betaka)
                                            <option value="{{ $betaka->EgazetBetakatTaqaddomID }}"
                                                {{ $person->EgazetBetakatTaqaddomID == $betaka->EgazetBetakatTaqaddomID ? 'selected' : '' }}>
                                                {{ $betaka->EgazetBetakatTaqaddomName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">{{ __('Scout sector') }}</label>
                                    <input type="text" readonly value="{{ $person->QetaaName ?? 'لا يوجد' }}"
                                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none">
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 md:p-6">
                            <div class="flex items-start justify-between gap-4 mb-5">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ __('Final part: Sector questions') }}</h2>
                                    <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">عرض الأسئلة مع إمكانية تعديل الإجابات المسجلة.
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 px-4 py-2 text-sm font-semibold">5
                                    / 5</span>
                            </div>

                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4 mb-4 text-slate-800 dark:text-slate-100">
                                <div class="font-bold">القطاع: {{ $person->QetaaName ?? 'لا يوجد' }}</div>
                            </div>

                            @if (!$questions->isEmpty())
                                <div class="space-y-4">
                                    @foreach ($questions as $question)
                                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                                            <div class="font-semibold text-slate-900 dark:text-slate-100 mb-2">
                                                السؤال: {{ $question->QuestionText }}
                                            </div>
                                            <div class="text-sm text-slate-600 dark:text-slate-300 mb-2">{{ __('Applicant answer') }}</div>
                                            <textarea name="questions[{{ $question->QuestionID }}]"
                                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-4 py-2.5 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                rows="3">{{ $question->Answer }}</textarea>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-5 text-amber-900 dark:text-amber-100">
                                    لا يوجد أسئلة لهذا الشخص في هذا القطاع
                                </div>
                            @endif
                        </section>

                        <div class="flex justify-center mt-8">
                            <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition duration-200">
                                حفظ التعديلات
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
