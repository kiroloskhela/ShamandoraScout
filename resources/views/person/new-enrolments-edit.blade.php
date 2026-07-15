<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>كشافة الشمندورة - تعديل بيانات</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 10px
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 999px
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"],
        input[type="url"],
        select,
        textarea {
            height: 50px;
        }

        textarea {
            height: auto;
        }
    </style>
</head>

<body class="min-h-screen bg-white py-8">

    <!-- Status -->
    @if (session('status'))
        <div class="max-w-6xl mx-auto px-4 mb-4">
            <div class="rounded-xl bg-emerald-600 text-white px-5 py-4 shadow">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('person.new-enrolments-update', $person->PersonID) }}"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="max-w-6xl mx-auto px-4">

            <div class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 overflow-hidden">

                <!-- Header -->
                <div class="px-6 md:px-10 py-8 border-b border-slate-200 bg-slate-50">
                    <div class="flex flex-col items-center justify-center gap-4 text-center">
                        <img src="{{ asset('img/shamandora.png') }}" alt="Logo" class="h-20 w-20 object-contain" />
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">تعديل بيانات الملتحق</h1>
                            <p class="text-slate-500 mt-2">تعديل جميع البيانات المسجلة</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-10 space-y-8">

                    <!-- ===================== Section 1: Personal ===================== -->
                    {{-- ===================== Section 1: Personal ===================== --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">{{ __('Part 1: Personal information') }}</h2>
                                <p class="text-slate-500 mt-1 text-sm">تعديل بيانات الملتحق الأساسية.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                1 / 5
                            </span>
                        </div>

                        @php
                            // الصور حسب الـ DB
                            $personalPath = $person->PersonalImagePath ?? null;
                            $scoutPath = $person->ScoutImagePath ?? null;

                            // Builder: يدعم URL كامل / مسارات public / أو ملفات storage public
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

                            // ==== NEW: Allergy / Medical history values ====
                            $allergyFood = trim((string) ($person->AllergyFood ?? ($person->allergy_food ?? '')));
                            $allergyMedicine = trim(
                                (string) ($person->AllergyMedicine ?? ($person->allergy_medicine ?? '')),
                            );
                            $medicalDiseases = trim(
                                (string) ($person->MedicalDiseases ?? ($person->medical_diseases ?? '')),
                            );
                            $medicalMedications = trim(
                                (string) ($person->MedicalMedications ?? ($person->medical_medications ?? '')),
                            );
                            $hasEmergency = $person->HasEmergencyCase ?? ($person->has_emergency_case ?? null);
                            $emergencyDetailsVal = trim(
                                (string) ($person->EmergencyDetails ?? ($person->emergency_details ?? '')),
                            );

                            $hasAllergy = $allergyFood !== '' || $allergyMedicine !== '';
                            $hasMedical =
                                $medicalDiseases !== '' ||
                                $medicalMedications !== '' ||
                                $hasEmergency == 1 ||
                                $hasEmergency === true ||
                                $hasEmergency === '1';

                            $yesNo = function ($v) {
                                return $v == 1 || $v === true || $v === '1' || $v === 'True' ? 'نعم' : 'لا';
                            };
                        @endphp

                        {{-- ✅ Photos INSIDE Section 1 --}}
                        @if ($personalUrl || $scoutUrl)
                            <div class="mb-6">
                                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <div class="font-bold text-slate-800">{{ __('Photos') }}</div>
                                        <div class="text-xs text-slate-500">سيتم عرض الصور المتاحة فقط</div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        @if ($personalUrl)
                                            <div class="md:col-span-6">
                                                <div class="rounded-2xl border border-slate-200 bg-white p-3">
                                                    <div class="text-sm font-semibold text-slate-700 mb-3">صورة شخصية
                                                    </div>
                                                    <div
                                                        class="rounded-2xl overflow-hidden ring-1 ring-slate-200 bg-white">
                                                        <img src="{{ $personalUrl }}" alt="Personal Photo"
                                                            class="w-full h-80 object-cover">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($scoutUrl)
                                            <div class="md:col-span-6">
                                                <div class="rounded-2xl border border-slate-200 bg-white p-3">
                                                    <div class="text-sm font-semibold text-slate-700 mb-3">صورة الزي
                                                        الرسمي
                                                    </div>
                                                    <div
                                                        class="rounded-2xl overflow-hidden ring-1 ring-slate-200 bg-white">
                                                        <img src="{{ $scoutUrl }}" alt="Scout Photo"
                                                            class="w-full h-80 object-cover">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ✅ NEW: Allergy + Medical History (show only if found) --}}
                        @if ($hasAllergy)
                            <div class="mb-6">
                                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <div class="font-bold text-slate-800">قسم الحساسية</div>
                                        <div class="text-xs text-slate-500">يظهر فقط عند وجود بيانات</div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        @if ($allergyFood !== '')
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 mb-1">حساسية من
                                                    طعام</label>
                                                <input type="text" name="allergy_food" value="{{ $allergyFood }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        @endif

                                        @if ($allergyMedicine !== '')
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 mb-1">حساسية من
                                                    دواء</label>
                                                <input type="text" name="allergy_medicine"
                                                    value="{{ $allergyMedicine }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($hasMedical)
                            <div class="mb-6">
                                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <div class="font-bold text-slate-800">قسم التاريخ المرضي</div>
                                        <div class="text-xs text-slate-500">يظهر فقط عند وجود بيانات</div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        @if ($medicalDiseases !== '')
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 mb-1">أمراض
                                                    مزمنة /
                                                    تشخيص</label>
                                                <input type="text" name="medical_diseases"
                                                    value="{{ $medicalDiseases }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        @endif

                                        @if ($medicalMedications !== '')
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-semibold text-slate-700 mb-1">الأدوية
                                                    الحالية</label>
                                                <input type="text" name="medical_medications"
                                                    value="{{ $medicalMedications }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        @endif

                                        @if ($hasEmergency !== null)
                                            <div class="md:col-span-12">
                                                <label class="block text-sm font-semibold text-slate-700 mb-1">هل يوجد
                                                    حالات
                                                    طوارئ سابقة؟</label>
                                                <select name="has_emergency_case"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="0" {{ $hasEmergency == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                                                    <option value="1" {{ $hasEmergency == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                                </select>
                                            </div>
                                        @endif

                                        @if ($emergencyDetailsVal !== '')
                                            <div class="md:col-span-12">
                                                <label class="block text-sm font-semibold text-slate-700 mb-1">تفاصيل
                                                    الحالة</label>
                                                <textarea name="emergency_details"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    rows="3">{{ $emergencyDetailsVal }}</textarea>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">تسلسل الطلب</label>
                                <input type="text" readonly value="{{ $person->PersonID }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('First name') }}</label>
                                <input type="text" name="first_name" value="{{ $person->FirstName }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Second name') }}</label>
                                <input type="text" name="second_name" value="{{ $person->SecondName }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Third name') }}</label>
                                <input type="text" name="third_name" value="{{ $person->ThirdName }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Fourth name') }}</label>
                                <input type="text" name="fourth_name" value="{{ $person->FourthName ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Applicant gender') }}</label>
                                <select name="gender"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="Male" {{ $person->Gender == 'Male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                    <option value="Female" {{ $person->Gender == 'Female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                </select>
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">البريد
                                    الإلكتروني</label>
                                <input type="email" name="personal_email"
                                    value="{{ $person->PersonalEmail ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Date of birth') }}</label>
                                <input type="date" name="birthdate_input" value="{{ $person->DateOfBirth }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Joining year') }}</label>
                                <input type="number" name="joining_year_input"
                                    value="{{ $person->ScoutJoiningYear ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('National ID') }}</label>
                                <input type="text" name="input_raqam_qawmy" value="{{ $person->RaqamQawmy }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    dir="ltr" required minlength="14" maxlength="14">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Facebook</label>
                                <input type="url" name="facebook_profile_url"
                                    value="{{ $person->FacebookProfileURL ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Instagram</label>
                                <input type="url" name="instagram_profile_url"
                                    value="{{ $person->InstagramProfileURL ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Blood type') }}</label>
                                <select name="blood_type_input"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">{{ __('Choose blood type') }}</option>
                                    @foreach ($blood as $b)
                                        <option value="{{ $b->BloodTypeID }}"
                                            {{ $person->BloodTypeID == $b->BloodTypeID ? 'selected' : '' }}>
                                            {{ $b->BloodTypeName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- ===================== Section 2: Contact ===================== -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الثاني: بيانات التواصل</h2>
                                <p class="text-slate-500 mt-1 text-sm">تعديل الأرقام والعنوان.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                2 / 5
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">موبايل الملتحق
                                    (الأساسي)</label>
                                <input type="text" name="personal_phone_number"
                                    value="{{ $person->PersonPersonalMobileNumber }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    dir="ltr" required minlength="11" maxlength="11">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Father mobile') }}</label>
                                <input type="text" name="father_mobile_number"
                                    value="{{ $person->FatherMobileNumber ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Mother mobile') }}</label>
                                <input type="text" name="mother_mobile_number"
                                    value="{{ $person->MotherMobileNumber ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">هاتف أرضي</label>
                                <input type="text" name="home_phone_number"
                                    value="{{ $person->HomePhoneNumber ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">هل الرقم الأساسي عليه
                                    Whatsapp؟</label>
                                <select name="is_personal_phone_has_whatsapp"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="0"
                                        {{ $person->IsOPersonalPhoneNumberHavingWhatsapp == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                                    <option value="1"
                                        {{ $person->IsOPersonalPhoneNumberHavingWhatsapp == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                </select>
                            </div>

                            <!-- Address box -->
                            <div class="md:col-span-12 mt-2">
                                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                    <div class="font-bold text-slate-800 mb-3">{{ __('Address') }}</div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">رقم
                                                العمارة</label>
                                            <input type="text" name="building_number"
                                                value="{{ $person->BuildingNumber ?? '' }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">رقم
                                                الدور</label>
                                            <input type="text" name="floor_number"
                                                value="{{ $person->FloorNumber ?? '' }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">رقم
                                                الشقة</label>
                                            <input type="text" name="appartment_number"
                                                value="{{ $person->AppartmentNumber ?? '' }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">اسم
                                                الشارع</label>
                                            <input type="text" name="sub_street_name"
                                                value="{{ $person->SubStreetName ?? '' }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">أقرب شارع
                                                رئيسي</label>
                                            <input type="text" name="main_street_name"
                                                value="{{ $person->MainStreetName ?? '' }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div class="md:col-span-12">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">أقرب علامة
                                                مميزة</label>
                                            <input type="text" name="nearest_landmark"
                                                value="{{ $person->NearestLandmark ?? '' }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div class="md:col-span-6">
                                            <label
                                                class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Area') }}</label>
                                            <select name="manteqa_id"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">اختر المنطقة</option>
                                                @foreach ($manateq as $m)
                                                    <option value="{{ $m->ManteqaID }}"
                                                        {{ $person->ManteqaID == $m->ManteqaID ? 'selected' : '' }}>
                                                        {{ $m->ManteqaName }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('District') }}</label>
                                            <select name="district_id"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">اختر الحي</option>
                                                @foreach ($districts as $d)
                                                    <option value="{{ $d->DistrictID }}"
                                                        {{ $person->DistrictID == $d->DistrictID ? 'selected' : '' }}>
                                                        {{ $d->DistrictName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </section>

                    <!-- ===================== Section 3: Education/Church ===================== -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الثالث: البيانات الدراسية والكنسية
                                </h2>
                                <p class="text-slate-500 mt-1 text-sm">تعديل بيانات تعليمية وكنسية (إن وُجدت).</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                3 / 5
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">السنة والمرحلة
                                    الدراسية</label>
                                <select name="sana_marhala_id"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">اختر السنة والمرحلة</option>
                                    @foreach ($seneen_marahel as $sm)
                                        <option value="{{ $sm->SanaMarhalaID }}"
                                            {{ $person->SanaMarhalaID == $sm->SanaMarhalaID ? 'selected' : '' }}>
                                            {{ $sm->SanaMarhalaName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('School name') }}</label>
                                <input type="text" name="school_name" value="{{ $person->SchoolName ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">سنة التخرج من
                                    المدرسة</label>
                                <input type="number" name="school_graduation_year"
                                    value="{{ $person->SchoolGraduationYear ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Spiritual father name') }}</label>
                                <input type="text" name="spiritual_father_name"
                                    value="{{ $person->SpiritualFatherName ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">كنيسة الأب
                                    الروحي</label>
                                <input type="text" name="spiritual_father_church_name"
                                    value="{{ $person->SpiritualFatherChurchName ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </section>

                    <!-- ===================== Section 4: Scout ===================== -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">{{ __('Part 4: Scout information') }}</h2>
                                <p class="text-slate-500 mt-1 text-sm">تعديل القطاع.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                4 / 5
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Scout sector') }}</label>
                                <input type="text" readonly value="{{ $person->QetaaName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>
                        </div>
                    </section>

                    <!-- ===================== Section 5: Sector Questions ===================== -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">{{ __('Final part: Sector questions') }}</h2>
                                <p class="text-slate-500 mt-1 text-sm">تعديل الإجابات المسجلة.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                5 / 5
                            </span>
                        </div>

                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 mb-4 text-slate-800">
                            <div class="font-bold">القطاع: {{ $person->QetaaName ?? 'لا يوجد' }}</div>
                        </div>

                        @if (!$questions->isEmpty())
                            <div class="space-y-4">
                                @foreach ($questions as $question)
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <div class="font-semibold text-slate-900 mb-2">السؤال:
                                            {{ $question->QuestionText }}</div>
                                        <div class="text-sm text-slate-600 mb-2">{{ __('Applicant answer') }}</div>
                                        <textarea name="question_{{ $question->QuestionID }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            rows="3">{{ $question->Answer }}</textarea>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 text-amber-900">
                                لا يوجد أسئلة لهذا الشخص في هذا القطاع
                            </div>
                        @endif
                    </section>

                    <!-- Submit Button -->
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

</body>

</html>
