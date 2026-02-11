<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>كشافة الشمندورة - اظهار بيانات</title>

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
        input[type="url"] {
            height: 50px;
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

    <div class="max-w-6xl mx-auto px-4">

        <div class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 overflow-hidden">

            <!-- Header -->
            <div class="px-6 md:px-10 py-8 border-b border-slate-200 bg-slate-50">
                <div class="flex flex-col items-center justify-center gap-4 text-center">
                    <img src="{{ asset('img/shamandora.png') }}" alt="Logo" class="h-20 w-20 object-contain" />
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">بيانات الملتحق</h1>
                        <p class="text-slate-500 mt-2">عرض جميع البيانات المسجلة</p>
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-10 space-y-8">

                <!-- ===================== Section 1: Personal ===================== -->
                {{-- ===================== Section 1: Personal ===================== --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">الجزء الأول: البيانات الشخصية</h2>
                            <p class="text-slate-500 mt-1 text-sm">عرض بيانات الملتحق الأساسية.</p>
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
                                    <div class="font-bold text-slate-800">الصور</div>
                                    <div class="text-xs text-slate-500">سيتم عرض الصور المتاحة فقط</div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    @if ($personalUrl)
                                        <div class="md:col-span-6">
                                            <div class="rounded-2xl border border-slate-200 bg-white p-3">
                                                <div class="text-sm font-semibold text-slate-700 mb-3">صورة شخصية</div>
                                                <div class="rounded-2xl overflow-hidden ring-1 ring-slate-200 bg-white">
                                                    <img src="{{ $personalUrl }}" alt="Personal Photo"
                                                        class="w-full h-80 object-cover">
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($scoutUrl)
                                        <div class="md:col-span-6">
                                            <div class="rounded-2xl border border-slate-200 bg-white p-3">
                                                <div class="text-sm font-semibold text-slate-700 mb-3">صورة الزي الرسمي
                                                </div>
                                                <div class="rounded-2xl overflow-hidden ring-1 ring-slate-200 bg-white">
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
                                            <input type="text" readonly value="{{ $allergyFood }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                        </div>
                                    @endif

                                    @if ($allergyMedicine !== '')
                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">حساسية من
                                                دواء</label>
                                            <input type="text" readonly value="{{ $allergyMedicine }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
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
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">أمراض مزمنة /
                                                تشخيص</label>
                                            <input type="text" readonly value="{{ $medicalDiseases }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                        </div>
                                    @endif

                                    @if ($medicalMedications !== '')
                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">الأدوية
                                                الحالية</label>
                                            <input type="text" readonly value="{{ $medicalMedications }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                        </div>
                                    @endif

                                    @if ($hasEmergency !== null)
                                        <div class="md:col-span-12">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">هل يوجد حالات
                                                طوارئ سابقة؟</label>
                                            <input type="text" readonly value="{{ $yesNo($hasEmergency) }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                        </div>
                                    @endif

                                    @if ($emergencyDetailsVal !== '')
                                        <div class="md:col-span-12">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">تفاصيل
                                                الحالة</label>
                                            <input type="text" readonly value="{{ $emergencyDetailsVal }}"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
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
                            <label class="block text-sm font-semibold text-slate-700 mb-1">الاسم الأول</label>
                            <input type="text" readonly value="{{ $person->FirstName }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">الاسم الثاني</label>
                            <input type="text" readonly value="{{ $person->SecondName }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">الاسم الثالث</label>
                            <input type="text" readonly value="{{ $person->ThirdName }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">الاسم الرابع</label>
                            <input type="text" readonly value="{{ $person->FourthName ?? '' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                placeholder="لا يوجد">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">نوع الملتحق</label>
                            <input type="text" readonly value="{{ $person->Gender == 'Male' ? 'ذكر' : 'أنثى' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">البريد الإلكتروني</label>
                            <input type="text" readonly value="{{ $person->PersonalEmail ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                dir="ltr">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">تاريخ الميلاد</label>
                            <input type="text" readonly value="{{ $person->DateOfBirth }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">سنة الالتحاق</label>
                            <input type="text" readonly value="{{ $person->ScoutJoiningYear ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">الرقم القومي</label>
                            <input type="text" readonly value="{{ $person->RaqamQawmy }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Facebook</label>
                            <input type="text" readonly value="{{ $person->FacebookProfileURL ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                dir="ltr">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Instagram</label>
                            <input type="text" readonly value="{{ $person->InstagramProfileURL ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                dir="ltr">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">فصيلة الدم</label>
                            <input type="text" readonly value="{{ $person->BloodTypeName ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>
                    </div>
                </section>






                <!-- ===================== Section 2: Contact ===================== -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">الجزء الثاني: بيانات التواصل</h2>
                            <p class="text-slate-500 mt-1 text-sm">الأرقام والعنوان.</p>
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
                            <input type="text" readonly value="{{ $person->PersonPersonalMobileNumber }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                dir="ltr">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">موبايل الأب</label>
                            <input type="text" readonly value="{{ $person->FatherMobileNumber ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                dir="ltr">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">موبايل الأم</label>
                            <input type="text" readonly value="{{ $person->MotherMobileNumber ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                dir="ltr">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">هاتف أرضي</label>
                            <input type="text" readonly value="{{ $person->HomePhoneNumber ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                dir="ltr">
                        </div>

                        <div class="md:col-span-12">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">هل الرقم الأساسي عليه
                                Whatsapp؟</label>
                            <input type="text" readonly
                                value="{{ $person->IsOPersonalPhoneNumberHavingWhatsapp == true || $person->IsOPersonalPhoneNumberHavingWhatsapp == 'True' ? 'نعم' : 'لا' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <!-- Address box -->
                        <div class="md:col-span-12 mt-2">
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                <div class="font-bold text-slate-800 mb-3">العنوان</div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <div class="md:col-span-4">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">رقم
                                            العمارة</label>
                                        <input type="text" readonly
                                            value="{{ $person->BuildingNumber ?? 'لا يوجد' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                    </div>

                                    <div class="md:col-span-4">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">رقم
                                            الدور</label>
                                        <input type="text" readonly
                                            value="{{ $person->FloorNumber ?? 'لا يوجد' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                    </div>

                                    <div class="md:col-span-4">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">رقم
                                            الشقة</label>
                                        <input type="text" readonly
                                            value="{{ $person->AppartmentNumber ?? 'لا يوجد' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                    </div>

                                    <div class="md:col-span-6">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">اسم
                                            الشارع</label>
                                        <input type="text" readonly
                                            value="{{ $person->SubStreetName ?? 'لا يوجد' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                    </div>

                                    <div class="md:col-span-6">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">أقرب شارع
                                            رئيسي</label>
                                        <input type="text" readonly
                                            value="{{ $person->MainStreetName ?? 'لا يوجد' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                    </div>

                                    <div class="md:col-span-12">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">أقرب علامة
                                            مميزة</label>
                                        <input type="text" readonly
                                            value="{{ $person->NearestLandmark ?? 'لا يوجد' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none"
                                            placeholder="لا يوجد">
                                    </div>

                                    <div class="md:col-span-6">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">المنطقة</label>
                                        <input type="text" readonly
                                            value="{{ $person->ManteqaName ?? 'لا يوجد' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
                                    </div>

                                    <div class="md:col-span-6">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1">الحي</label>
                                        <input type="text" readonly
                                            value="{{ $person->DistrictName ?? 'لا يوجد' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none">
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
                            <h2 class="text-xl font-bold text-slate-900">الجزء الثالث: البيانات الدراسية والكنسية</h2>
                            <p class="text-slate-500 mt-1 text-sm">بيانات تعليمية وكنسية (إن وُجدت).</p>
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
                            <input type="text" readonly value="{{ $person->SanaMarhalaName ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">اسم المدرسة</label>
                            <input type="text" readonly value="{{ $person->SchoolName ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">سنة التخرج من
                                المدرسة</label>
                            <input type="text" readonly value="{{ $person->SchoolGraduationYear ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">اسم الأب الروحي</label>
                            <input type="text" readonly value="{{ $person->SpiritualFatherName ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">كنيسة الأب الروحي</label>
                            <input type="text" readonly
                                value="{{ $person->SpiritualFatherChurchName ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>
                    </div>
                </section>

                <!-- ===================== Section 4: Scout ===================== -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">الجزء الرابع: البيانات الكشفية</h2>
                            <p class="text-slate-500 mt-1 text-sm">بيانات القطاع.</p>
                        </div>
                        <span
                            class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                            4 / 5
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-12">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">القطاع الكشفي</label>
                            <input type="text" readonly value="{{ $person->QetaaName ?? 'لا يوجد' }}"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                        </div>
                    </div>
                </section>

                <!-- ===================== Section 5: Sector Questions ===================== -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">الجزء الأخير: الأسئلة المختصة بالقطاع</h2>
                            <p class="text-slate-500 mt-1 text-sm">الإجابات المسجلة.</p>
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
                                    <div class="text-sm text-slate-600 mb-2">إجابة الملتحق</div>
                                    <input type="text" readonly value="{{ $question->Answer }}"
                                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5 text-amber-900">
                            لا يوجد أسئلة لهذا الشخص في هذا القطاع
                        </div>
                    @endif
                </section>

            </div>
        </div>
    </div>

</body>

</html>
