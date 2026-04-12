@extends('layouts.app', ['pageTitle' => 'عرض بيانات الملتحق'])

@section('content')
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>كشافة الشمندورة - عرض بيانات الملتحق</title>

        <script src="https://cdn.tailwindcss.com"></script>
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
            textarea {
                min-height: 50px;
            }

            textarea {
                height: auto;
            }
        </style>
    </head>

    <body class="min-h-screen bg-white py-8">
        @if (session('status'))
            <div class="max-w-6xl mx-auto px-4 mb-4">
                <div class="rounded-xl bg-emerald-600 text-white px-5 py-4 shadow">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <div class="max-w-6xl mx-auto px-4">
            <div class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 overflow-hidden">

                <div class="px-6 md:px-10 py-8 border-b border-slate-200 bg-slate-50">
                    <div class="flex flex-col items-center justify-center gap-4 text-center">
                        <img src="{{ asset('img/shamandora.png') }}" alt="Logo" class="h-20 w-20 object-contain" />
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">بيانات الملتحق</h1>
                            <p class="text-slate-500 mt-2">عرض جميع البيانات المسجلة بنفس أسلوب صفحات الملتحقين الجدد</p>
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
                            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                <div class="flex items-center justify-between gap-3 mb-4">
                                    <div class="font-bold text-slate-800">الصور</div>
                                    <div class="text-xs text-slate-500">سيتم عرض الصور المتاحة فقط</div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    @if ($personalUrl)
                                        <div class="md:col-span-6">
                                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                <div class="font-semibold text-slate-800 mb-3">الصورة الشخصية</div>
                                                <img src="{{ $personalUrl }}" alt="الصورة الشخصية"
                                                    class="w-full h-80 object-cover rounded-xl border border-slate-200">
                                            </div>
                                        </div>
                                    @endif

                                    @if ($scoutUrl)
                                        <div class="md:col-span-6">
                                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                <div class="font-semibold text-slate-800 mb-3">الصورة الكشفية</div>
                                                <img src="{{ $scoutUrl }}" alt="الصورة الكشفية"
                                                    class="w-full h-80 object-cover rounded-xl border border-slate-200">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الأول: البيانات الشخصية</h2>
                                <p class="text-slate-500 mt-1 text-sm">عرض البيانات الأساسية للملتحق.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">1
                                / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الرقم التعريفي</label>
                                <input type="text" readonly value="{{ $person->PersonID }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">كود الشمندورة</label>
                                <input type="text" readonly value="{{ $person->ShamandoraCode ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">نوع الملتحق</label>
                                <input type="text" readonly value="{{ $person->Gender == 'Male' ? 'ذكر' : 'أنثى' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">فصيلة الدم</label>
                                <input type="text" readonly value="{{ $person->BloodTypeName ?? 'لا يوجد' }}"
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
                                <input type="text" readonly value="{{ $person->FourthName ?? 'لا يوجد' }}"
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
                                <input type="text" readonly value="{{ $person->DateOfBirth ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">سنة الالتحاق</label>
                                <input type="text" readonly value="{{ $person->ScoutJoiningYear ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الرقم القومي</label>
                                <input type="text" readonly value="{{ $person->RaqamQawmy ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">لينك فيسبوك</label>
                                <input type="text" readonly value="{{ $person->FacebookProfileURL ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                    dir="ltr">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">لينك انستجرام</label>
                                <input type="text" readonly value="{{ $person->InstagramProfileURL ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                    dir="ltr">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الثاني: بيانات التواصل والعنوان</h2>
                                <p class="text-slate-500 mt-1 text-sm">عرض أرقام التواصل والعنوان.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">2
                                / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الموبايل الشخصي</label>
                                <input type="text" readonly
                                    value="{{ $person->PersonPersonalMobileNumber ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">موبايل الأب</label>
                                <input type="text" readonly value="{{ $person->FatherMobileNumber ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">موبايل الأم</label>
                                <input type="text" readonly value="{{ $person->MotherMobileNumber ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">التليفون الأرضي</label>
                                <input type="text" readonly value="{{ $person->HomePhoneNumber ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">واتساب على الرقم
                                    الأساسي</label>
                                <input type="text" readonly
                                    value="{{ (string) ($person->IsOPersonalPhoneNumberHavingWhatsapp ?? '') === '1' ? 'نعم' : 'لا' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">رقم المبنى</label>
                                <input type="text" readonly value="{{ $person->BuildingNumber ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">رقم الدور</label>
                                <input type="text" readonly value="{{ $person->FloorNumber ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">رقم الشقة</label>
                                <input type="text" readonly value="{{ $person->AppartmentNumber ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الشارع الرئيسي</label>
                                <input type="text" readonly value="{{ $person->MainStreetName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الشارع الفرعي</label>
                                <input type="text" readonly value="{{ $person->SubStreetName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">أقرب علامة مميزة</label>
                                <input type="text" readonly value="{{ $person->NearestLandmark ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">المنطقة</label>
                                <input type="text" readonly value="{{ $person->ManteqaName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الحي</label>
                                <input type="text" readonly value="{{ $person->DistrictName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الثالث: البيانات الدراسية والكنسية</h2>
                                <p class="text-slate-500 mt-1 text-sm">عرض البيانات التعليمية والكنسية.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">3
                                / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">السنة والمرحلة
                                    الدراسية</label>
                                <input type="text" readonly value="{{ $person->SanaMarhalaName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">اسم الوظيفة</label>
                                <input type="text" readonly value="{{ $person->JobName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">مكان العمل</label>
                                <input type="text" readonly value="{{ $person->WorkPlace ?? 'لا يوجد' }}"
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
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الكلية</label>
                                <input type="text" readonly value="{{ $person->FacultyName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الجامعة</label>
                                <input type="text" readonly value="{{ $person->UniversityName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">سنة التخرج من
                                    الجامعة</label>
                                <input type="text" readonly
                                    value="{{ $person->ActualFacultyGraduationYear ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">اسم الأب الروحي</label>
                                <input type="text" readonly value="{{ $person->SpiritualFatherName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">كنيسة الأب الروحي / أب
                                    الاعتراف</label>
                                <input type="text" readonly
                                    value="{{ $person->SpiritualFatherChurchName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الرابع: البيانات الكشفية</h2>
                                <p class="text-slate-500 mt-1 text-sm">بيانات القطاع والرتبة والإجازة.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">4
                                / 5</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الرتبة الكشفية</label>
                                <input type="text" readonly value="{{ $person->RotbaName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">إجازة بطاقة التقدم</label>
                                <input type="text" readonly
                                    value="{{ $person->EgazetBetakatTaqaddomName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">القطاع الكشفي</label>
                                <input type="text" readonly value="{{ $person->QetaaName ?? 'لا يوجد' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الأخير: الأسئلة المختصة بالقطاع</h2>
                                <p class="text-slate-500 mt-1 text-sm">عرض الأسئلة وإجاباتها المسجلة بوضوح.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">5
                                / 5</span>
                        </div>

                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 mb-4 text-slate-800">
                            <div class="font-bold">القطاع: {{ $person->QetaaName ?? 'لا يوجد' }}</div>
                        </div>

                        @if (!$questions->isEmpty())
                            <div class="space-y-4">
                                @foreach ($questions as $question)
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <div class="font-semibold text-slate-900 mb-2">
                                            السؤال: {{ $question->QuestionText }}
                                        </div>
                                        <div class="text-sm text-slate-600 mb-2">إجابة الملتحق</div>
                                        <textarea readonly rows="3"
                                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">{{ $question->Answer }}</textarea>
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
@endsection
