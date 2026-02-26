<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>كشافة الشمندورة | إدخال بيانات</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

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
        input[type="url"],
        select {
            height: 50px;
        }

        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: left 0.75rem center;
            background-size: 1.25rem;
            padding-left: 2.5rem;
            line-height: 50px;
        }

        select option {
            text-align: right;
            direction: rtl;
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

            <!-- Header: logo top middle + centered title -->
            <div class="px-6 md:px-10 py-8 border-b border-slate-200 bg-slate-50">
                <div class="flex flex-col items-center justify-center gap-4 text-center">

                    <!-- Logo -->
                    <img src="{{ asset('img/shamandora.png') }}" alt="Logo" class="h-20 w-20 object-contain" />

                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                            إدخال بيانات ملتحق جديد
                        </h1>
                        <p class="text-slate-500 mt-2">
                            الحقول المطلوبة عليها علامة <span class="font-bold text-rose-700">*</span>
                        </p>
                    </div>

                </div>
            </div>

            <div class="p-6 md:p-10">
                <form id="regForm2" method="POST" action="{{ route('person.liveform-insert-person') }}" novalidate
                    enctype="multipart/form-data">
                    @csrf

                    <!-- ============================ Section 1 ============================ -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الأول: البيانات الشخصية</h2>
                                <p class="text-slate-500 mt-1 text-sm">أدخل بيانات الملتحق الأساسية.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                1 / 4
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <!-- names -->
<div class="md:col-span-3">
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        الاسم الأول <span class="text-rose-700">*</span>
        <span class="text-xs text-slate-500">(بالعربي)</span>
    </label>
    <input required id="first_name" name="first_name" type="text"
        pattern="^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]+$"
        oninput="this.value=this.value.replace(/[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]/g,'')"
        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        placeholder="الاسم الأول">
    <p class="error hidden mt-1 text-sm text-rose-700">
        يرجى إدخال أحرف عربية فقط
    </p>
</div>

<div class="md:col-span-3">
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        الاسم الثاني <span class="text-rose-700">*</span>
        <span class="text-xs text-slate-500">(بالعربي)</span>
    </label>
    <input required id="second_name" name="second_name" type="text"
        pattern="^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]+$"
        oninput="this.value=this.value.replace(/[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]/g,'')"
        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        placeholder="الاسم الثاني">
    <p class="error hidden mt-1 text-sm text-rose-700">
        يرجى إدخال أحرف عربية فقط
    </p>
</div>

<div class="md:col-span-3">
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        الاسم الثالث <span class="text-rose-700">*</span>
        <span class="text-xs text-slate-500">(بالعربي)</span>
    </label>
    <input required id="third_name" name="third_name" type="text"
        pattern="^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]+$"
        oninput="this.value=this.value.replace(/[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]/g,'')"
        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        placeholder="الاسم الثالث">
    <p class="error hidden mt-1 text-sm text-rose-700">
        يرجى إدخال أحرف عربية فقط
    </p>
</div>

<div class="md:col-span-3">
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        الاسم الرابع
        <span class="text-xs text-slate-500">(بالعربي)</span>
    </label>
    <input id="fourth_name" name="fourth_name" type="text"
        pattern="^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]+$"
        oninput="this.value=this.value.replace(/[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]/g,'')"
        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        placeholder="اختياري">
</div>

                            <!-- gender -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    نوع الملتحق <span class="text-rose-700">*</span>
                                </label>
                                <select required id="gender" name="gender"
                                    class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @if ($gender == 'Male')
                                        <option value="Male" selected>ذكر</option>
                                        <option value="Female">أنثى</option>
                                    @else
                                        <option value="Female" selected>أنثى</option>
                                        <option value="Male">ذكر</option>
                                    @endif
                                </select>
                                <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                            </div>

                            <!-- email -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">البريد الإلكتروني</label>
                                <input id="email_input" name="email_input" type="email" dir="ltr"
                                    class="field-email w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="example@email.com">
                                <p class="error-email hidden mt-1 text-sm text-rose-700">البريد الإلكتروني غير صحيح</p>
                            </div>

                            <!-- birthdate -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    تاريخ الميلاد <span class="text-rose-700">*</span>
                                </label>
                                <input required id="birthdate_input" name="birthdate_input" type="date"
                                    class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                            </div>

                            <!-- joining year -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">سنة الالتحاق</label>
                                <select id="joining_year_input" name="joining_year_input"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">

                                    @for ($year = date('Y'); $year >= 2000; $year--)
                                        <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor

                                </select>
                            </div>


                            <!-- national id -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    الرقم القومي (14 رقم) <span class="text-rose-700">*</span>
                                </label>
                                <input required id="input_raqam_qawmy" name="input_raqam_qawmy" inputmode="numeric"
                                    pattern="\d{14}" maxlength="14" type="text"
                                    class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="أدخل 14 رقم">
                                <p class="error hidden mt-1 text-sm text-rose-700" data-error="nid">
                                    الرقم القومي يجب أن يكون 14 رقم
                                </p>
                            </div>

                            <!-- Facebook -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">رابط Facebook (إن
                                    وُجد)</label>
                                <input id="inputFacebookLink" name="inputFacebookLink" type="url"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="https://facebook.com/...">
                            </div>

                            <!-- Instagram -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">رابط Instagram (إن
                                    وُجد)</label>
                                <input id="inputInstagramLink" name="inputInstagramLink" type="url"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="https://instagram.com/...">
                            </div>

                            <!-- blood type -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    فصيلة الدم <span class="text-rose-700">*</span>
                                    <span class="text-xs text-slate-500 font-normal ms-2">اختر "غير محدد" عند عدم
                                        التأكد</span>
                                </label>
                                <select required id="blood_type_input" name="blood_type_input"
                                    class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="" disabled selected>اختر فصيلة الدم</option>
                                    @foreach ($blood as $blood_element)
                                        <option value="{{ $blood_element->BloodTypeID }}">
                                            {{ $blood_element->BloodTypeName }}</option>
                                    @endforeach
                                </select>
                                <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                            </div>
                        </div>
                    </section>

                    <div class="my-8 h-px bg-slate-200"></div>

                    <div class="my-8 h-px bg-slate-200"></div>

                    <!-- ============================ NEW SECTION: Allergy ============================ -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">قسم الحساسية</h2>
                                <p class="text-slate-500 mt-1 text-sm">اكتب الحساسية إن وُجدت (افصل بين العناصر
                                    بفاصلة).</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                صحي
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <!-- Food Allergy (Dropdown + Other text) -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    حساسية من طعام (إن وُجد)
                                </label>

                                <select id="allergy_food_select"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="" selected>لا يوجد / اختر...</option>
                                    <option value="بقوليات (فول)">بقوليات (فول)</option>
                                    <option value="لبن">لبن</option>
                                    <option value="سمك">سمك</option>
                                    <option value="فراولة">فراولة</option>
                                    <option value="أخرى">أخرى</option>
                                </select>

                                <!-- shown only if "أخرى" -->
                                <div id="allergy_food_other_wrap" class="hidden mt-3">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">اكتب نوع
                                        الاكل</label>
                                    <input id="allergy_food_other" type="text"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="مثال: موز">
                                    <p class="mt-2 text-xs text-slate-500">سيتم حفظه ضمن حساسية الطعام.</p>
                                </div>

                                <!-- this is what gets submitted to backend -->
                                <input type="hidden" id="allergy_food" name="allergy_food" value="">
                            </div>

                            <script>
                                (function() {
                                    const select = document.getElementById('allergy_food_select');
                                    const otherWrap = document.getElementById('allergy_food_other_wrap');
                                    const otherInput = document.getElementById('allergy_food_other');
                                    const hidden = document.getElementById('allergy_food');

                                    function syncFoodAllergy() {
                                        const v = (select.value || '').trim();

                                        if (!v) {
                                            hidden.value = '';
                                            otherWrap.classList.add('hidden');
                                            otherInput.value = '';
                                            return;
                                        }

                                        if (v === 'أخرى') {
                                            otherWrap.classList.remove('hidden');
                                            hidden.value = (otherInput.value || '').trim(); // store what user types
                                        } else {
                                            otherWrap.classList.add('hidden');
                                            otherInput.value = '';
                                            hidden.value = v;
                                        }
                                    }

                                    // init + events
                                    select.addEventListener('change', syncFoodAllergy);
                                    otherInput.addEventListener('input', syncFoodAllergy);

                                    syncFoodAllergy();
                                })();
                            </script>


                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    حساسية من دواء (إن وُجد)
                                </label>
                                <input id="allergy_medicine" name="allergy_medicine" type="text"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="مثال: بنسلين">
                                <p class="mt-2 text-xs text-slate-500">يمكنك كتابة أكثر من دواء مفصول بفاصلة.</p>
                            </div>
                        </div>
                    </section>

                    <div class="my-8 h-px bg-slate-200"></div>

                    <!-- ============================ NEW SECTION: Medical History ============================ -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">قسم التاريخ المرضي</h2>
                                <p class="text-slate-500 mt-1 text-sm">اختياري — يساعدنا في الحالات الطارئة.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                صحي
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">أمراض مزمنة / تشخيص (إن
                                    وُجد)</label>
                                <input id="medical_diseases" name="medical_diseases" type="text"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="مثال: ربو، سكر، ضغط">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الأدوية الحالية (إن
                                    وُجد)</label>
                                <input id="medical_medications" name="medical_medications" type="text"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="مثال: بخاخ، أنسولين">
                            </div>

                            <div class="md:col-span-12">
                                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                        <div>
                                            <label
                                                class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                                                <input id="has_emergency_case" name="has_emergency_case"
                                                    type="checkbox" value="1"
                                                    class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                هل يوجد حالات طوارئ سابقة؟
                                            </label>
                                            <p class="text-xs text-slate-500 mt-1">مثل: حساسية شديدة، إغماء، دخول
                                                مستشفى…</p>
                                        </div>

                                        <div class="w-full md:w-2/3">
                                            <input id="emergency_details" name="emergency_details" type="text"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="اكتب تفاصيل الحالة (إذا نعم)">
                                            <p id="emergency_details_error" class="hidden mt-1 text-sm text-rose-700">
                                                من فضلك اكتب تفاصيل الحالة لأنك اخترت "نعم"
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>


                    <div class="my-8 h-px bg-slate-200"></div>

                    <!-- ============================ Section 2 ============================ -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الثاني: بيانات التواصل</h2>
                                <p class="text-slate-500 mt-1 text-sm">أدخل أرقام التواصل والعنوان.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                2 / 4
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <!-- phones -->
                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    موبايل الملتحق (11 رقم) <span class="text-rose-700">*</span>
                                </label>
                                <input required id="personal_phone_number" name="personal_phone_number"
                                    inputmode="numeric" pattern="\d{11}" maxlength="11" type="text"
                                    class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="مثال: 01xxxxxxxxx">
                                <p class="error hidden mt-1 text-sm text-rose-700" data-error="phone">
                                    رقم الموبايل يجب أن يكون 11 رقم
                                </p>
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">موبايل الأب (إن
                                    وُجد)</label>
                                <input id="father_phone_number" name="father_phone_number" type="text"
                                    inputmode="numeric"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="اختياري">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">موبايل الأم (إن
                                    وُجد)</label>
                                <input id="mother_phone_number" name="mother_phone_number" type="text"
                                    inputmode="numeric"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="اختياري">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">هاتف أرضي (إن
                                    وُجد)</label>
                                <input id="home_phone_number" name="home_phone_number" type="text"
                                    inputmode="numeric"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="اختياري">
                            </div>

                            <!-- whatsapp -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">هل رقم الموبايل الأساسي
                                    عليه Whatsapp؟</label>
                                <select id="has_whatsapp" name="has_whatsapp"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="" disabled selected>اختر نعم أم لا</option>
                                    <option value="True">نعم</option>
                                    <option value="False">لا</option>
                                </select>
                            </div>

                            <!-- address -->
                            <div class="md:col-span-12 mt-2">
                                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="font-bold text-slate-800">العنوان</h3>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                                رقم العمارة <span class="text-rose-700">*</span>
                                            </label>
                                            <input required id="building_number" name="building_number"
                                                type="text" inputmode="numeric"
                                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="رقم العمارة">
                                            <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                                        </div>

                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                                رقم الدور <span class="text-rose-700">*</span>
                                            </label>
                                            <input required id="floor_number" name="floor_number" type="text"
                                                inputmode="numeric"
                                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="رقم الدور">
                                            <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                                        </div>

                                        <div class="md:col-span-4">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                                رقم الشقة <span class="text-rose-700">*</span>
                                            </label>
                                            <input required id="appartment_number" name="appartment_number"
                                                type="text" inputmode="numeric"
                                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="رقم الشقة">
                                            <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                                اسم الشارع <span class="text-rose-700">*</span>
                                            </label>
                                            <input required id="sub_street_name" name="sub_street_name"
                                                type="text"
                                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="اسم الشارع">
                                            <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                                اسم أقرب شارع رئيسي <span class="text-rose-700">*</span>
                                            </label>
                                            <input required id="main_street_name" name="main_street_name"
                                                type="text"
                                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="أقرب شارع رئيسي">
                                            <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                                        </div>

                                        <div class="md:col-span-12">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">أقرب علامة
                                                مميزة</label>
                                            <input id="nearest_landmark" name="nearest_landmark" type="text"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="اختياري">
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                                المنطقة <span class="text-rose-700">*</span>
                                            </label>
                                            <select required id="manteqa_id" name="manteqa_id"
                                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <option value="" disabled selected>اختر المنطقة السكنية</option>
                                                @foreach ($manateq as $manteqa)
                                                    <option value="{{ $manteqa->ManteqaID }}">
                                                        {{ $manteqa->ManteqaName }}</option>
                                                @endforeach
                                            </select>
                                            <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                                        </div>

                                        <div class="md:col-span-6">
                                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                                الحي <span class="text-rose-700">*</span>
                                            </label>
                                            <select required id="district_id" name="district_id"
                                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                <option value="" disabled selected>اختر الحي</option>
                                                @foreach ($districts as $district)
                                                    <option value="{{ $district->DistrictID }}">
                                                        {{ $district->DistrictName }}</option>
                                                @endforeach
                                            </select>
                                            <p class="error hidden mt-1 text-sm text-rose-700">هذا الحقل مطلوب</p>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </section>

                    <div class="my-8 h-px bg-slate-200"></div>




                    <!-- ============================ Section 3 ============================ -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الثالث: البيانات الدراسية والكنسية
                                </h2>
                                <p class="text-slate-500 mt-1 text-sm">البيانات التعليمية والكنسية (إن وُجدت).</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                3 / 4
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">السنة والمرحلة
                                    الدراسية</label>
                                <select
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    name="sana_marhala_id" id="sana_marhala_id">
                                    <option value="{{ $sana_marhala_id }}" selected>{{ $sana_marhala_name }}</option>
                                </select>
                            </div>

                            <div class="md:col-span-8">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">اسم المدرسة</label>
                                <input id="person_school" name="person_school" type="text"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="أدخل اسم المدرسة">
                            </div>

                            <div class="md:col-span-4">
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        سنة التخرج من المدرسة
    </label>
    <select
        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        name="school_grad_year" id="school_grad_year">
        
        <option value="" disabled selected>
            اختر سنة التخرج من المدرسة
        </option>

        @for ($i = 1970; $i <= 2050; $i++)
            <option value="{{ $i }}">{{ $i }}</option>
        @endfor

    </select>
</div>

                            @if ($sana_marhala_id > 14)
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">اسم الكلية</label>
                                    <select
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        name="person_faculty" id="person_faculty">
                                        <option value="" disabled selected>اختر الكلية</option>
                                        @foreach ($faculties as $faculty)
                                            <option value="{{ $faculty->FacultyID }}">{{ $faculty->FacultyName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">اسم الجامعة</label>
                                    <select
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        name="person_university" id="person_university">
                                        <option value="" disabled selected>اختر الجامعة</option>
                                        @foreach ($universities as $university)
                                            <option value="{{ $university->UniversityID }}">
                                                {{ $university->UniversityName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-6">
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        سنة التخرج من الجامعة
    </label>
    <select
        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        name="university_grad_year" id="university_grad_year">

        <option value="" disabled selected>
            اختر سنة التخرج من الجامعة
        </option>

        @for ($i = 1970; $i <= 2050; $i++)
            <option value="{{ $i }}">{{ $i }}</option>
        @endfor

    </select>
</div>
                            @endif

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">الأب الروحي / أب
                                    الاعتراف</label>
                                <input id="spiritual_father" name="spiritual_father" type="text"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="أدخل الاسم">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">كنيسة الأب الروحي / أب
                                    الاعتراف</label>
                                <input id="spiritual_father_church" name="spiritual_father_church" type="text"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="أدخل الكنيسة">
                            </div>
                        </div>
                    </section>

                    <div class="my-8 h-px bg-slate-200"></div>

                    <!-- ============================ PHOTOS SECTION ============================ -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">قسم الصور</h2>
                                <p class="text-slate-500 mt-1 text-sm">ارفع صورة شخصية وصورة الزي الرسمي (إن وُجد).</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                صور
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                            <!-- Profile image -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    صورة شخصية
                                    <span class="text-xs text-slate-500 font-normal ms-2">JPG/PNG/WebP - حد أقصى 5
                                        ميجا</span>
                                </label>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4" data-upload>
                                    <div class="flex items-center gap-4">

                                        <div
                                            class="h-24 w-24 rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                            <img data-preview class="hidden h-full w-full object-cover"
                                                alt="">
                                            <svg data-placeholder class="h-10 w-10 text-slate-300" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <input type="file" name="profile_image"
                                                accept="image/jpeg,image/png,image/webp" class="hidden" data-file
                                                data-error-key="profile_image" data-max-size="5242880">

                                            <div class="flex flex-col gap-2">
                                                <button type="button" data-pick
                                                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-white font-semibold hover:bg-indigo-700">
                                                    اختيار ملف
                                                </button>

                                                <p class="text-xs text-slate-600" data-filename>لم يتم اختيار ملف</p>

                                                <p class="error-photo hidden mt-1 text-sm text-rose-700"
                                                    data-error="profile_image">
                                                    الصورة يجب أن تكون بصيغة JPG/PNG/WebP وبحجم أقل من 5 ميجا
                                                </p>
                                            </div>

                                            <p class="mt-2 text-xs text-slate-500">يفضل صورة واضحة للوجه بخلفية بيضاء
                                            </p>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- Uniform image -->
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">
                                    صورة الزي الرسمي
                                    <span class="text-xs text-slate-500 font-normal ms-2">اختياري - JPG/PNG/WebP - حد
                                        أقصى 5 ميجا</span>
                                </label>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4" data-upload>
                                    <div class="flex items-center gap-4">

                                        <div
                                            class="h-24 w-24 rounded-2xl bg-white ring-1 ring-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                            <img data-preview class="hidden h-full w-full object-cover"
                                                alt="">
                                            <svg data-placeholder class="h-10 w-10 text-slate-300" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <input type="file" name="scout_uniform_image"
                                                accept="image/jpeg,image/png,image/webp" class="hidden" data-file
                                                data-error-key="scout_uniform_image" data-max-size="5242880">

                                            <div class="flex flex-col gap-2">
                                                <button type="button" data-pick
                                                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-white font-semibold hover:bg-indigo-700">
                                                    اختيار ملف
                                                </button>

                                                <p class="text-xs text-slate-600" data-filename>لم يتم اختيار ملف</p>

                                                <p class="error-photo hidden mt-1 text-sm text-rose-700"
                                                    data-error="scout_uniform_image">
                                                    الصورة يجب أن تكون بصيغة JPG/PNG/WebP وبحجم أقل من 5 ميجا
                                                </p>
                                            </div>

                                            <p class="mt-2 text-xs text-slate-500">اختياري — صورة كاملة بالزي الكشفي إن
                                                أمكن</p>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </section>

                    <div class="my-8 h-px bg-slate-200"></div>

                    <!-- ============================ Section 4 ============================ -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الرابع: البيانات الكشفية</h2>
                                <p class="text-slate-500 mt-1 text-sm">تحديد القطاع الكشفي ثم المتابعة لباقي الأسئلة.
                                </p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                4 / 4
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">القطاع الكشفي</label>
                                <select
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    name="qetaa_id" id="qetaa_id">
                                    <option value="{{ $qetaa_id }}" selected>{{ $qetaa_name }}</option>
                                </select>
                                <label name="qetaa_name" id="qetaa_name" value="{{ $qetaa_name }}" hidden></label>
                            </div>

                            <div class="md:col-span-12">
                                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-amber-900">
                                    <div class="font-bold mb-1">تنبيه مهم</div>
                                    <div class="text-sm leading-relaxed">
                                        برجاء التأكد من البيانات مرة أخرى قبل ضغط "استمرار".
                                        سيتم الانتقال إلى باقي الأسئلة الخاصة بالقطاع بعد الضغط.
                                    </div>
                                </div>
                            </div>

                            <div class="md:col-span-12 flex justify-end mt-2">
                                <button id="submitBtn" type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-700 px-10 py-3.5 font-bold text-white shadow hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span>استمرار</span>
                                    <span>→</span>
                                </button>
                            </div>
                        </div>
                    </section>

                </form>
            </div>
        </div>
    </div>


    <script>
        // =========================
        // Global refs
        // =========================
        const form = document.getElementById('regForm2');
        const submitBtn = document.getElementById('submitBtn');

        // =========================
        // Constants
        // =========================
        const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5MB
        const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

        // Track touched fields (for validation UX)
        const touchedFields = new Set();

        // =========================
        // Helpers: text / email / digits
        // =========================
        function onlyDigits(value) {
            return (value || '').replace(/\D/g, '');
        }

        function normalizeEmail(value) {
            return (value || '')
                .trim()
                .replace(/[\u200E\u200F\u061C\u202A-\u202E]/g, '');
        }

        function isValidEmail(email) {
            return /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(email);
        }

        // =========================
        // UI helpers: input errors
        // =========================
        function showError(el, show, customKey = null) {
            const wrapper = el.closest('div');
            if (!wrapper) return;

            if (show) {
                el.classList.add('border-rose-600', 'ring-2', 'ring-rose-200');
                el.classList.remove('border-slate-300');
            } else {
                el.classList.remove('border-rose-600', 'ring-2', 'ring-rose-200');
                el.classList.add('border-slate-300');
            }

            const requiredMsg = wrapper.querySelector('.error');
            if (requiredMsg && !customKey) requiredMsg.classList.toggle('hidden', !show);
            if (requiredMsg && customKey) requiredMsg.classList.add('hidden');

            const phoneMsg = wrapper.querySelector('[data-error="phone"]');
            const nidMsg = wrapper.querySelector('[data-error="nid"]');

            if (phoneMsg) phoneMsg.classList.add('hidden');
            if (nidMsg) nidMsg.classList.add('hidden');

            if (show && customKey === 'phone' && phoneMsg) phoneMsg.classList.remove('hidden');
            if (show && customKey === 'nid' && nidMsg) nidMsg.classList.remove('hidden');
        }

        function showEmailError(el, show) {
            const wrapper = el.closest('div');
            if (!wrapper) return;

            if (show) {
                el.classList.add('border-rose-600', 'ring-2', 'ring-rose-200');
                el.classList.remove('border-slate-300');
            } else {
                el.classList.remove('border-rose-600', 'ring-2', 'ring-rose-200');
                el.classList.add('border-slate-300');
            }

            const emailMsg = wrapper.querySelector('.error-email');
            if (emailMsg) emailMsg.classList.toggle('hidden', !show);
        }

        // =========================
        // Validation: fields
        // =========================
        function validateRequired(el) {
            if (el.hasAttribute('required') && !el.value.trim()) {
                showError(el, true);
                return false;
            }
            showError(el, false);
            return true;
        }

        function validatePhone(el) {
            el.value = onlyDigits(el.value).slice(0, 11);
            if (!el.value.trim()) return validateRequired(el);
            const ok = el.value.length === 11;
            showError(el, !ok, 'phone');
            return ok;
        }

        function validateNID(el) {
            el.value = onlyDigits(el.value).slice(0, 14);
            if (!el.value.trim()) return validateRequired(el);
            const ok = el.value.length === 14;
            showError(el, !ok, 'nid');
            return ok;
        }

        function validateEmailField(el) {
            const cleaned = normalizeEmail(el.value);
            el.value = cleaned;

            if (!cleaned) {
                showEmailError(el, false);
                return true; // optional
            }

            const ok = isValidEmail(cleaned);
            showEmailError(el, !ok);
            return ok;
        }

        function validateField(el) {
            if (!touchedFields.has(el.id)) return true;
            if (!el.classList.contains('field') && !el.classList.contains('field-email')) return true;

            if (el.id === 'personal_phone_number') return validatePhone(el);
            if (el.id === 'input_raqam_qawmy') return validateNID(el);
            if (el.id === 'email_input') return validateEmailField(el);

            return validateRequired(el);
        }

        // =========================
        // Emergency details logic
        // =========================
        const emergencyCheckbox = document.getElementById('has_emergency_case');
        const emergencyDetails = document.getElementById('emergency_details');
        const emergencyDetailsError = document.getElementById('emergency_details_error');

        function validateEmergencyDetails() {
            if (!emergencyCheckbox || !emergencyDetails) return true;

            const needs = emergencyCheckbox.checked;
            const val = (emergencyDetails.value || '').trim();

            if (needs && !val) {
                if (emergencyDetailsError) emergencyDetailsError.classList.remove('hidden');
                emergencyDetails.classList.add('border-rose-600', 'ring-2', 'ring-rose-200');
                emergencyDetails.classList.remove('border-slate-300');
                return false;
            } else {
                if (emergencyDetailsError) emergencyDetailsError.classList.add('hidden');
                emergencyDetails.classList.remove('border-rose-600', 'ring-2', 'ring-rose-200');
                emergencyDetails.classList.add('border-slate-300');
                return true;
            }
        }

        if (emergencyCheckbox && emergencyDetails) {
            emergencyCheckbox.addEventListener('change', () => {
                touchedFields.add('emergency_details');
                validateEmergencyDetails();
                validateAll();
            });

            emergencyDetails.addEventListener('input', () => {
                touchedFields.add('emergency_details');
                validateEmergencyDetails();
                validateAll();
            });
        }

        // =========================
        // Allergy dropdown sync (if exists)
        // =========================
        (function initAllergyFoodOther() {
            const select = document.getElementById('allergy_food_select');
            const otherWrap = document.getElementById('allergy_food_other_wrap');
            const otherInput = document.getElementById('allergy_food_other');
            const hidden = document.getElementById('allergy_food');

            if (!select || !otherWrap || !otherInput || !hidden) return;

            function syncFoodAllergy() {
                const v = (select.value || '').trim();

                if (!v) {
                    hidden.value = '';
                    otherWrap.classList.add('hidden');
                    otherInput.value = '';
                    return;
                }

                if (v === 'أخرى') {
                    otherWrap.classList.remove('hidden');
                    hidden.value = (otherInput.value || '').trim();
                } else {
                    otherWrap.classList.add('hidden');
                    otherInput.value = '';
                    hidden.value = v;
                }
            }

            select.addEventListener('change', syncFoodAllergy);
            otherInput.addEventListener('input', syncFoodAllergy);
            syncFoodAllergy();
        })();

        // =========================
        // Photos: UI + compression
        // =========================
        function formatFileSize(bytes) {
            if (!bytes) return '0 بايت';
            const k = 1024;
            const sizes = ['بايت', 'كيلو بايت', 'ميجا بايت'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
        }

        function resetPhotoUI(root) {
            const input = root.querySelector('input[data-file]');
            const img = root.querySelector('[data-preview]');
            const placeholder = root.querySelector('[data-placeholder]');
            const filename = root.querySelector('[data-filename]');
            const errorKey = input?.getAttribute('data-error-key') || '';
            const err = errorKey ? root.querySelector(`[data-error="${errorKey}"]`) : null;

            if (err) err.classList.add('hidden');

            if (img) {
                img.src = '';
                img.classList.add('hidden');
            }
            if (placeholder) placeholder.classList.remove('hidden');
            if (filename) filename.textContent = 'لم يتم اختيار ملف';
        }

        function showPhotoError(root, msg) {
            const input = root.querySelector('input[data-file]');
            const errorKey = input?.getAttribute('data-error-key') || '';
            const err = errorKey ? root.querySelector(`[data-error="${errorKey}"]`) : null;
            if (err) {
                err.textContent = msg;
                err.classList.remove('hidden');
            }
        }

        function hidePhotoError(root) {
            const input = root.querySelector('input[data-file]');
            const errorKey = input?.getAttribute('data-error-key') || '';
            const err = errorKey ? root.querySelector(`[data-error="${errorKey}"]`) : null;
            if (err) err.classList.add('hidden');
        }

        function setPhotoPreview(root, file) {
            const img = root.querySelector('[data-preview]');
            const placeholder = root.querySelector('[data-placeholder]');
            const filename = root.querySelector('[data-filename]');

            if (filename) filename.textContent = file ? file.name : 'لم يتم اختيار ملف';

            if (!file) {
                resetPhotoUI(root);
                return;
            }

            const url = URL.createObjectURL(file);
            if (img) {
                img.src = url;
                img.classList.remove('hidden');
            }
            if (placeholder) placeholder.classList.add('hidden');
        }

        function replaceInputFile(input, file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
        }

        function loadImageFromFile(file) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                const url = URL.createObjectURL(file);
                img.onload = () => {
                    URL.revokeObjectURL(url);
                    resolve(img);
                };
                img.onerror = () => {
                    URL.revokeObjectURL(url);
                    reject(new Error('image-load-failed'));
                };
                img.src = url;
            });
        }

        function canvasToBlob(canvas, mime, quality) {
            return new Promise((resolve) => {
                canvas.toBlob((blob) => resolve(blob), mime, quality);
            });
        }

        function browserSupportsWebp() {
            try {
                const c = document.createElement('canvas');
                return c.toDataURL('image/webp').startsWith('data:image/webp');
            } catch {
                return false;
            }
        }

        async function compressToUnder5MB(file, maxBytes) {
            // If already OK, keep it
            if (file.size <= maxBytes) return {
                ok: true,
                file
            };

            // Choose target mime (WebP usually smaller)
            const targetMime = browserSupportsWebp() ? 'image/webp' : 'image/jpeg';

            const img = await loadImageFromFile(file);

            // Step 1: resize (big impact)
            const maxDimList = [2048, 1600, 1280, 1024]; // try progressively smaller
            const qualityList = [0.82, 0.75, 0.68, 0.60, 0.52, 0.45]; // try progressively lower

            for (const maxDim of maxDimList) {
                const scale = Math.min(1, maxDim / Math.max(img.width, img.height));
                const w = Math.max(1, Math.round(img.width * scale));
                const h = Math.max(1, Math.round(img.height * scale));

                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;

                const ctx = canvas.getContext('2d', {
                    alpha: false
                });
                ctx.drawImage(img, 0, 0, w, h);

                // Step 2: try qualities
                for (const q of qualityList) {
                    const blob = await canvasToBlob(canvas, targetMime, q);
                    if (!blob) continue;

                    if (blob.size <= maxBytes) {
                        const ext = targetMime === 'image/webp' ? 'webp' : 'jpg';
                        const safeBase = (file.name || 'image').replace(/\.[^.]+$/, '');
                        const newName = `${safeBase}-compressed.${ext}`;

                        const newFile = new File([blob], newName, {
                            type: targetMime
                        });
                        return {
                            ok: true,
                            file: newFile
                        };
                    }
                }
            }

            // Failed to compress under limit
            return {
                ok: false,
                file
            };
        }

        async function handlePhotoChange(root) {
            const input = root.querySelector('input[data-file]');
            if (!input) return;

            hidePhotoError(root);

            if (!input.files || !input.files[0]) {
                resetPhotoUI(root);
                return;
            }

            const file = input.files[0];

            // Type check
            if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
                showPhotoError(root, 'الصورة يجب أن تكون بصيغة JPG أو PNG أو WebP');
                input.value = '';
                resetPhotoUI(root);
                return;
            }

            // If too big: compress
            if (file.size > MAX_IMAGE_BYTES) {
                showPhotoError(root, `جارٍ ضغط الصورة... (الحجم الحالي ${formatFileSize(file.size)})`);

                try {
                    const result = await compressToUnder5MB(file, MAX_IMAGE_BYTES);

                    if (!result.ok) {
                        // IMPORTANT: avoid 413 by preventing submit
                        showPhotoError(root,
                            `لم نتمكن من ضغط الصورة لأقل من 5 ميجا. الحجم الحالي: ${formatFileSize(file.size)}. برجاء اختيار صورة أصغر.`
                            );
                        input.value = '';
                        resetPhotoUI(root);
                        validateAll();
                        return;
                    }

                    // Replace input file with compressed file
                    replaceInputFile(input, result.file);
                    showPhotoError(root, `تم ضغط الصورة بنجاح ✅ الحجم: ${formatFileSize(result.file.size)}`);
                    setPhotoPreview(root, result.file);
                    validateAll();
                    return;
                } catch (e) {
                    showPhotoError(root, 'حدث خطأ أثناء ضغط الصورة. برجاء اختيار صورة أخرى.');
                    input.value = '';
                    resetPhotoUI(root);
                    validateAll();
                    return;
                }
            }

            // Normal preview
            setPhotoPreview(root, file);
            validateAll();
        }

        function validateFilesBeforeSubmit() {
            let ok = true;

            document.querySelectorAll('[data-upload]').forEach(root => {
                const input = root.querySelector('input[data-file]');
                if (!input || !input.files || !input.files[0]) return;

                const file = input.files[0];

                // type guard
                if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
                    showPhotoError(root, 'الصورة يجب أن تكون بصيغة JPG أو PNG أو WebP');
                    ok = false;
                    return;
                }

                // size guard: if still > 5MB => block submit (prevents 413)
                if (file.size > MAX_IMAGE_BYTES) {
                    showPhotoError(root,
                        `حجم الصورة ما زال أكبر من 5 ميجا (${formatFileSize(file.size)}). برجاء اختيار صورة أصغر.`
                        );
                    ok = false;
                }
            });

            return ok;
        }

        // Init upload widgets
        document.querySelectorAll('[data-upload]').forEach(root => {
            const pickBtn = root.querySelector('[data-pick]');
            const input = root.querySelector('input[data-file]');
            if (!pickBtn || !input) return;

            resetPhotoUI(root);

            pickBtn.addEventListener('click', () => input.click());
            input.addEventListener('change', () => {
                // async handler (compression)
                handlePhotoChange(root);
            });
        });

        // =========================
        // validateAll
        // =========================
        function validateAll() {
            let ok = true;

            // required fields
            const fields = form.querySelectorAll('.field[required]');
            fields.forEach(el => {
                if (touchedFields.has(el.id) && !validateField(el)) ok = false;
            });

            // email field
            const emailField = document.getElementById('email_input');
            if (emailField && touchedFields.has(emailField.id)) {
                if (!validateEmailField(emailField)) ok = false;
            }

            // emergency section
            if (!validateEmergencyDetails()) ok = false;

            // photos must be <= 5MB
            if (!validateFilesBeforeSubmit()) ok = false;

            submitBtn.disabled = !ok;
            return ok;
        }

        // =========================
        // Events for field validation
        // =========================
        form.addEventListener('blur', (e) => {
            const el = e.target;
            if (!el.classList.contains('field') && !el.classList.contains('field-email')) return;

            touchedFields.add(el.id);
            validateField(el);
            validateAll();
        }, true);

        form.addEventListener('input', (e) => {
            const el = e.target;
            if (!touchedFields.has(el.id)) return;

            if (el.id === 'personal_phone_number') {
                if (el.value.trim()) validatePhone(el);
            } else if (el.id === 'input_raqam_qawmy') {
                if (el.value.trim()) validateNID(el);
            } else if (el.id === 'email_input') {
                validateEmailField(el);
            } else {
                validateField(el);
            }

            validateAll();
        });

        // =========================
        // Submit: block if invalid + avoid 413
        // =========================
        form.addEventListener('submit', async (e) => {
            // mark everything touched
            const allFields = form.querySelectorAll('.field[required], .field-email');
            allFields.forEach(el => touchedFields.add(el.id));

            let ok = true;

            // validate required
            const requiredFields = form.querySelectorAll('.field[required]');
            requiredFields.forEach(el => {
                if (!validateField(el)) ok = false;
            });

            // validate email
            const emailField = document.getElementById('email_input');
            if (emailField && emailField.value.trim() && !validateEmailField(emailField)) ok = false;

            // emergency details
            if (!validateEmergencyDetails()) ok = false;

            // Last safety: if user selected >5MB and compression still running / failed => block
            if (!validateFilesBeforeSubmit()) ok = false;

            if (!ok) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.ring-rose-200') || form.querySelector(
                    '.error-photo:not(.hidden)');
                if (firstInvalid) firstInvalid.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return;
            }
        });

        // Initial
        submitBtn.disabled = false;
    </script>




</body>

</html>
