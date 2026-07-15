<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>كشافة الشمندورة | إدخال بيانات</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#0f766e',
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
        }

        .sea-bg {
            background:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(13, 148, 136, 0.25), transparent),
                linear-gradient(165deg, #f0fdfa 0%, #ecfeff 40%, #f8fafc 100%);
        }

        ::-webkit-scrollbar {
            width: 10px
        }

        ::-webkit-scrollbar-thumb {
            background: #99f6e4;
            border-radius: 999px
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #5eead4
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
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230f766e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
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

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="sea-bg min-h-screen py-8">

    @if (session('status'))
        <div class="max-w-4xl mx-auto px-4 mb-4">
            <div class="rounded-xl bg-emerald-600 text-white px-5 py-4 shadow">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <div class="max-w-4xl mx-auto px-4" x-data="{ step: 1 }" x-cloak id="wizardRoot">
        <div class="rounded-3xl bg-white/90 shadow-xl ring-1 ring-teal-100 overflow-hidden backdrop-blur">

            <div class="px-6 md:px-10 py-8 border-b border-teal-100 bg-gradient-to-b from-teal-50/80 to-white">
                <div class="flex flex-col items-center justify-center gap-4 text-center">
                    <img src="{{ asset('img/shamandora.png') }}" alt="الشمندورة"
                        class="h-20 w-20 object-contain drop-shadow-md" />
                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-brand-900">
                            طلب التحاق جديد
                        </h1>
                        <p class="text-slate-600 mt-2 text-sm md:text-base">
                            الحقول المطلوبة عليها علامة <span class="font-bold text-rose-600">*</span>
                        </p>
                    </div>
                </div>

                <!-- Horizontal stepper (RTL) -->
                <nav class="mt-8" aria-label="خطوات التسجيل">
                    <ol class="flex items-center justify-between gap-1 sm:gap-2">
                        <template x-for="(label, i) in [
                            'البيانات الشخصية',
                            'بيانات ولي الأمر',
                            'البيانات الدراسية',
                            'المراجعة'
                        ]" :key="i">
                            <li class="flex-1 flex flex-col items-center gap-2 min-w-0">
                                <div class="flex items-center w-full">
                                    <div class="hidden sm:block flex-1 h-0.5 rounded"
                                        :class="i === 0 ? 'bg-transparent' : (step > i ? 'bg-brand-600' : 'bg-slate-200')"></div>
                                    <button type="button"
                                        @click="if (i + 1 < step) step = i + 1"
                                        class="relative z-10 flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold transition"
                                        :class="step === i + 1
                                            ? 'bg-brand-700 text-white shadow-md shadow-teal-900/20 ring-4 ring-teal-100'
                                            : (step > i + 1
                                                ? 'bg-brand-600 text-white'
                                                : 'bg-white text-slate-400 ring-1 ring-slate-200')">
                                        <span x-text="i + 1"></span>
                                    </button>
                                    <div class="hidden sm:block flex-1 h-0.5 rounded"
                                        :class="i === 3 ? 'bg-transparent' : (step > i + 1 ? 'bg-brand-600' : 'bg-slate-200')"></div>
                                </div>
                                <span class="text-[10px] sm:text-xs font-bold text-center leading-tight px-0.5"
                                    :class="step === i + 1 ? 'text-brand-800' : 'text-slate-400'"
                                    x-text="label"></span>
                            </li>
                        </template>
                    </ol>
                </nav>
            </div>

            <div class="p-6 md:p-10">
                <form id="regForm2" method="POST" action="{{ route('person.liveform-step2-save') }}" novalidate
                    enctype="multipart/form-data">
                    @csrf

                    <!-- ============================ STEP 1: Personal ============================ -->
                    <div data-step="1" x-show="step === 1" x-transition.opacity.duration.200ms>

                        <section class="rounded-2xl border border-teal-100 bg-white p-5 md:p-6">
                            <div class="mb-5">
                                <h2 class="text-xl font-bold text-brand-900">البيانات الشخصية</h2>
                                <p class="text-slate-500 mt-1 text-sm">أدخل بيانات الملتحق الأساسية والصورة الشخصية.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        الاسم الأول <span class="text-rose-600">*</span>
                                        <span class="text-xs text-slate-500">(بالعربي)</span>
                                    </label>
                                    <input required id="first_name" name="first_name" type="text" lang="ar"
                                        dir="rtl" pattern="^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]+$"
                                        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                        placeholder="الاسم الأول">
                                    <p class="error hidden mt-1 text-sm text-rose-600">يرجى إدخال أحرف عربية فقط</p>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        الاسم الثاني <span class="text-rose-600">*</span>
                                        <span class="text-xs text-slate-500">(بالعربي)</span>
                                    </label>
                                    <input required id="second_name" name="second_name" type="text" lang="ar"
                                        dir="rtl" pattern="^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]+$"
                                        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                        placeholder="الاسم الثاني">
                                    <p class="error hidden mt-1 text-sm text-rose-600">يرجى إدخال أحرف عربية فقط</p>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        الاسم الثالث <span class="text-rose-600">*</span>
                                        <span class="text-xs text-slate-500">(بالعربي)</span>
                                    </label>
                                    <input required id="third_name" name="third_name" type="text" lang="ar"
                                        dir="rtl" pattern="^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]+$"
                                        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                        placeholder="الاسم الثالث">
                                    <p class="error hidden mt-1 text-sm text-rose-600">يرجى إدخال أحرف عربية فقط</p>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        الاسم الرابع
                                        <span class="text-xs text-slate-500">(بالعربي)</span>
                                    </label>
                                    <input id="fourth_name" name="fourth_name" type="text" lang="ar" dir="rtl"
                                        pattern="^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]+$"
                                        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                        placeholder="اختياري">
                                    <p class="error hidden mt-1 text-sm text-rose-600">يرجى إدخال أحرف عربية فقط</p>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        نوع الملتحق <span class="text-rose-600">*</span>
                                    </label>
                                    <select required id="gender" name="gender" class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                        @if ($gender == 'Male')
                                            <option value="Male" selected>ذكر</option>
                                            <option value="Female">أنثى</option>
                                        @else
                                            <option value="Female" selected>أنثى</option>
                                            <option value="Male">ذكر</option>
                                        @endif
                                    </select>
                                    <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">البريد الإلكتروني</label>
                                    <input id="email_input" name="email_input" type="email" dir="ltr"
                                        class="field-email w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                        placeholder="example@email.com">
                                    <p class="error-email hidden mt-1 text-sm text-rose-600">البريد الإلكتروني غير صحيح</p>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        تاريخ الميلاد <span class="text-rose-600">*</span>
                                    </label>
                                    <input required id="birthdate_input" name="birthdate_input" type="date"
                                        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                    <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">سنة الالتحاق</label>
                                    <select id="joining_year_input" name="joining_year_input" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                        @for ($year = date('Y'); $year >= 2000; $year--)
                                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        الرقم القومي (14 رقم) <span class="text-rose-600">*</span>
                                    </label>
                                    <input required id="input_raqam_qawmy" name="input_raqam_qawmy" inputmode="numeric"
                                        pattern="\d{14}" maxlength="14" type="text"
                                        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                        placeholder="أدخل 14 رقم">
                                    <p class="error hidden mt-1 text-sm text-rose-600" data-error="nid">
                                        الرقم القومي يجب أن يكون 14 رقم و ان يكون صحيح
                                    </p>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        موبايل الملتحق (11 رقم) <span class="text-rose-600">*</span>
                                    </label>
                                    <input required id="personal_phone_number" name="personal_phone_number"
                                        inputmode="numeric" pattern="\d{11}" maxlength="11" type="text"
                                        class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                        placeholder="مثال: 01xxxxxxxxx">
                                    <p class="error hidden mt-1 text-sm text-rose-600" data-error="phone">
                                        رقم الموبايل يجب أن يكون 11 رقم
                                    </p>
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">رابط Facebook (إن وُجد)</label>
                                    <input id="inputFacebookLink" name="inputFacebookLink" type="url"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                        placeholder="https://facebook.com/...">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">رابط Instagram (إن وُجد)</label>
                                    <input id="inputInstagramLink" name="inputInstagramLink" type="url"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                        placeholder="https://instagram.com/...">
                                </div>

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        فصيلة الدم <span class="text-rose-600">*</span>
                                        <span class="text-xs text-slate-500 font-normal ms-2">اختر "غير محدد" عند عدم التأكد</span>
                                    </label>
                                    <select required id="blood_type_input" name="blood_type_input" class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                        <option value="" disabled selected>اختر فصيلة الدم</option>
                                        @foreach ($blood as $blood_element)
                                            <option value="{{ $blood_element->BloodTypeID }}">
                                                {{ $blood_element->BloodTypeName }}</option>
                                        @endforeach
                                    </select>
                                    <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                </div>

                                <!-- Profile image -->
                                <div class="md:col-span-12">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        صورة شخصية
                                        <span class="text-xs text-slate-500 font-normal ms-2">JPG/PNG/WebP - حد أقصى 5 ميجا</span>
                                    </label>
                                    <div class="rounded-2xl border border-teal-100 bg-teal-50/40 p-4" data-upload>
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="h-24 w-24 rounded-2xl bg-white ring-1 ring-teal-100 overflow-hidden flex items-center justify-center shrink-0">
                                                <img data-preview class="hidden h-full w-full object-cover" alt="">
                                                <svg data-placeholder class="h-10 w-10 text-teal-300" fill="none"
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
                                                        class="inline-flex items-center justify-center rounded-xl bg-brand-700 px-4 py-2 text-white font-bold hover:bg-brand-800 w-fit">
                                                        اختيار ملف
                                                    </button>
                                                    <p class="text-xs text-slate-600" data-filename>لم يتم اختيار ملف</p>
                                                    <p class="error-photo hidden mt-1 text-sm text-rose-600"
                                                        data-error="profile_image">
                                                        الصورة يجب أن تكون بصيغة JPG/PNG/WebP وبحجم أقل من 5 ميجا
                                                    </p>
                                                </div>
                                                <p class="mt-2 text-xs text-slate-500">يفضل صورة واضحة للوجه بخلفية بيضاء</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-teal-100 bg-white p-5 md:p-6 mt-6">
                            <div class="mb-5">
                                <h2 class="text-xl font-bold text-brand-900">قسم الحساسية</h2>
                                <p class="text-slate-500 mt-1 text-sm">اكتب الحساسية إن وُجدت (افصل بين العناصر بفاصلة).</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">حساسية من طعام (إن وُجد)</label>
                                    <select id="allergy_food_select" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                        <option value="" selected>لا يوجد / اختر...</option>
                                        <option value="بقوليات (فول)">بقوليات (فول)</option>
                                        <option value="لبن">لبن</option>
                                        <option value="سمك">سمك</option>
                                        <option value="فراولة">فراولة</option>
                                        <option value="أخرى">أخرى</option>
                                    </select>
                                    <div id="allergy_food_other_wrap" class="hidden mt-3">
                                        <label class="block text-sm font-bold text-slate-700 mb-1">اكتب نوع الاكل</label>
                                        <input id="allergy_food_other" type="text" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                            placeholder="مثال: موز">
                                        <p class="mt-2 text-xs text-slate-500">سيتم حفظه ضمن حساسية الطعام.</p>
                                    </div>
                                    <input type="hidden" id="allergy_food" name="allergy_food" value="">
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">حساسية من دواء (إن وُجد)</label>
                                    <input id="allergy_medicine" name="allergy_medicine" type="text"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="مثال: بنسلين">
                                    <p class="mt-2 text-xs text-slate-500">يمكنك كتابة أكثر من دواء مفصول بفاصلة.</p>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-teal-100 bg-white p-5 md:p-6 mt-6">
                            <div class="mb-5">
                                <h2 class="text-xl font-bold text-brand-900">قسم التاريخ المرضي</h2>
                                <p class="text-slate-500 mt-1 text-sm">اختياري — يساعدنا في الحالات الطارئة.</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">أمراض مزمنة / تشخيص (إن وُجد)</label>
                                    <input id="medical_diseases" name="medical_diseases" type="text"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="مثال: ربو، سكر، ضغط">
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">الأدوية الحالية (إن وُجد)</label>
                                    <input id="medical_medications" name="medical_medications" type="text"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="مثال: بخاخ، أنسولين">
                                </div>
                                <div class="md:col-span-12">
                                    <div class="rounded-2xl bg-teal-50/50 border border-teal-100 p-4">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                            <div>
                                                <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                                                    <input id="has_emergency_case" name="has_emergency_case"
                                                        type="checkbox" value="1"
                                                        class="h-5 w-5 rounded border-slate-300 text-brand-700 focus:ring-brand-600">
                                                    هل يوجد حالات طوارئ سابقة؟
                                                </label>
                                                <p class="text-xs text-slate-500 mt-1">مثل: حساسية شديدة، إغماء، دخول مستشفى…</p>
                                            </div>
                                            <div class="w-full md:w-2/3">
                                                <input id="emergency_details" name="emergency_details" type="text"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                                    placeholder="اكتب تفاصيل الحالة (إذا نعم)">
                                                <p id="emergency_details_error" class="hidden mt-1 text-sm text-rose-600">
                                                    من فضلك اكتب تفاصيل الحالة لأنك اخترت "نعم"
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="mt-8 flex justify-end">
                            <button type="button" data-next-step="1"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-700 px-8 py-3.5 font-bold text-white shadow-md shadow-teal-900/10 hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 transition">
                                التالي ←
                            </button>
                        </div>
                    </div>

                    <!-- ============================ STEP 2: Guardian / Contact ============================ -->
                    <div data-step="2" x-show="step === 2" x-transition.opacity.duration.200ms>

                        <section class="rounded-2xl border border-teal-100 bg-white p-5 md:p-6">
                            <div class="mb-5">
                                <h2 class="text-xl font-bold text-brand-900">بيانات ولي الأمر</h2>
                                <p class="text-slate-500 mt-1 text-sm">أرقام التواصل والعنوان.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">موبايل الأب (إن وُجد)</label>
                                    <input id="father_phone_number" name="father_phone_number" type="text"
                                        inputmode="numeric" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="اختياري">
                                </div>
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">موبايل الأم (إن وُجد) <span class="text-rose-600">*</span></label>
                                    <input id="mother_phone_number" name="mother_phone_number" type="text"
                                        inputmode="numeric" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="اختياري">
                                </div>
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">هاتف أرضي (إن وُجد)</label>
                                    <input id="home_phone_number" name="home_phone_number" type="text"
                                        inputmode="numeric" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="اختياري">
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">هل رقم الموبايل الأساسي عليه Whatsapp؟</label>
                                    <select id="has_whatsapp" name="has_whatsapp" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                        <option value="" disabled selected>اختر نعم أم لا</option>
                                        <option value="True">نعم</option>
                                        <option value="False">لا</option>
                                    </select>
                                </div>

                                <div class="md:col-span-12 mt-2">
                                    <div class="rounded-2xl bg-teal-50/40 border border-teal-100 p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h3 class="font-bold text-brand-900">العنوان</h3>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                            <div class="md:col-span-4">
                                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                                    رقم العمارة <span class="text-rose-600">*</span>
                                                </label>
                                                <input required id="building_number" name="building_number"
                                                    type="text" inputmode="numeric" class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                                    placeholder="رقم العمارة">
                                                <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                            </div>
                                            <div class="md:col-span-4">
                                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                                    رقم الدور <span class="text-rose-600">*</span>
                                                </label>
                                                <input required id="floor_number" name="floor_number" type="text"
                                                    inputmode="numeric" class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                                    placeholder="رقم الدور">
                                                <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                            </div>
                                            <div class="md:col-span-4">
                                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                                    رقم الشقة <span class="text-rose-600">*</span>
                                                </label>
                                                <input required id="appartment_number" name="appartment_number"
                                                    type="text" inputmode="numeric" class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                                    placeholder="رقم الشقة">
                                                <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                            </div>
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                                    اسم الشارع <span class="text-rose-600">*</span>
                                                </label>
                                                <input required id="sub_street_name" name="sub_street_name"
                                                    type="text" class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                                    placeholder="اسم الشارع">
                                                <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                            </div>
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                                    اسم أقرب شارع رئيسي <span class="text-rose-600">*</span>
                                                </label>
                                                <input required id="main_street_name" name="main_street_name"
                                                    type="text" class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                                    placeholder="أقرب شارع رئيسي">
                                                <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                            </div>
                                            <div class="md:col-span-12">
                                                <label class="block text-sm font-bold text-slate-700 mb-1">أقرب علامة مميزة</label>
                                                <input id="nearest_landmark" name="nearest_landmark" type="text"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="اختياري">
                                            </div>
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                                    المنطقة <span class="text-rose-600">*</span>
                                                </label>
                                                <select required id="manteqa_id" name="manteqa_id" class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                                    <option value="" disabled selected>اختر المنطقة السكنية</option>
                                                    @foreach ($manateq as $manteqa)
                                                        <option value="{{ $manteqa->ManteqaID }}">
                                                            {{ $manteqa->ManteqaName }}</option>
                                                    @endforeach
                                                </select>
                                                <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                            </div>
                                            <div class="md:col-span-6">
                                                <label class="block text-sm font-bold text-slate-700 mb-1">
                                                    الحي <span class="text-rose-600">*</span>
                                                </label>
                                                <select required id="district_id" name="district_id" class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                                    <option value="" disabled selected>اختر الحي</option>
                                                    @foreach ($districts as $district)
                                                        <option value="{{ $district->DistrictID }}">
                                                            {{ $district->DistrictName }}</option>
                                                    @endforeach
                                                </select>
                                                <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <div class="mt-8 flex justify-between gap-3">
                            <button type="button" @click="step = 1"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 py-3.5 font-bold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-600 transition">
                                → السابق
                            </button>
                            <button type="button" data-next-step="2"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-700 px-8 py-3.5 font-bold text-white shadow-md shadow-teal-900/10 hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 transition">
                                التالي ←
                            </button>
                        </div>
                    </div>

                    <!-- ============================ STEP 3: Academic + scout ============================ -->
                    <div data-step="3" x-show="step === 3" x-transition.opacity.duration.200ms>

                        <section class="rounded-2xl border border-teal-100 bg-white p-5 md:p-6">
                            <div class="mb-5">
                                <h2 class="text-xl font-bold text-brand-900">البيانات الدراسية</h2>
                                <p class="text-slate-500 mt-1 text-sm">البيانات التعليمية والكنسية والكشفية.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-12">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">السنة والمرحلة الدراسية</label>
                                    <select class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" name="sana_marhala_id" id="sana_marhala_id">
                                        <option value="{{ $sana_marhala_id }}" selected>{{ $sana_marhala_name }}</option>
                                    </select>
                                </div>
                                <div class="md:col-span-8">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">اسم المدرسة</label>
                                    <input id="person_school" name="person_school" type="text"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="أدخل اسم المدرسة">
                                </div>
                                <div class="md:col-span-4">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">سنة التخرج من المدرسة</label>
                                    <select class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" name="school_grad_year" id="school_grad_year">
                                        <option value="" disabled selected>اختر سنة التخرج من المدرسة</option>
                                        @for ($i = 1970; $i <= 2050; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>

                                @if ($sana_marhala_id > 14)
                                    <div class="md:col-span-6">
                                        <label class="block text-sm font-bold text-slate-700 mb-1">اسم الكلية</label>
                                        <select class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" name="person_faculty" id="person_faculty">
                                            <option value="" disabled selected>اختر الكلية</option>
                                            @foreach ($faculties as $faculty)
                                                <option value="{{ $faculty->FacultyID }}">{{ $faculty->FacultyName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-6">
                                        <label class="block text-sm font-bold text-slate-700 mb-1">اسم الجامعة</label>
                                        <select class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" name="person_university" id="person_university">
                                            <option value="" disabled selected>اختر الجامعة</option>
                                            @foreach ($universities as $university)
                                                <option value="{{ $university->UniversityID }}">
                                                    {{ $university->UniversityName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-6">
                                        <label class="block text-sm font-bold text-slate-700 mb-1">سنة التخرج من الجامعة</label>
                                        <select class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" name="university_grad_year" id="university_grad_year">
                                            <option value="" disabled selected>اختر سنة التخرج من الجامعة</option>
                                            @for ($i = 1970; $i <= 2050; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                @endif

                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">الأب الروحي / أب الاعتراف</label>
                                    <input id="spiritual_father" name="spiritual_father" type="text"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="أدخل الاسم">
                                </div>
                                <div class="md:col-span-6">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">كنيسة الأب الروحي / أب الاعتراف</label>
                                    <input id="spiritual_father_church" name="spiritual_father_church" type="text"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" placeholder="أدخل الكنيسة">
                                </div>

                                <div class="md:col-span-12">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        صورة الزي الرسمي
                                        <span class="text-xs text-slate-500 font-normal ms-2">اختياري - JPG/PNG/WebP - حد أقصى 5 ميجا</span>
                                    </label>
                                    <div class="rounded-2xl border border-teal-100 bg-teal-50/40 p-4" data-upload>
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="h-24 w-24 rounded-2xl bg-white ring-1 ring-teal-100 overflow-hidden flex items-center justify-center shrink-0">
                                                <img data-preview class="hidden h-full w-full object-cover" alt="">
                                                <svg data-placeholder class="h-10 w-10 text-teal-300" fill="none"
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
                                                        class="inline-flex items-center justify-center rounded-xl bg-brand-700 px-4 py-2 text-white font-bold hover:bg-brand-800 w-fit">
                                                        اختيار ملف
                                                    </button>
                                                    <p class="text-xs text-slate-600" data-filename>لم يتم اختيار ملف</p>
                                                    <p class="error-photo hidden mt-1 text-sm text-rose-600"
                                                        data-error="scout_uniform_image">
                                                        الصورة يجب أن تكون بصيغة JPG/PNG/WebP وبحجم أقل من 5 ميجا
                                                    </p>
                                                </div>
                                                <p class="mt-2 text-xs text-slate-500">اختياري — صورة كاملة بالزي الكشفي إن أمكن</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-12">
                                    <label class="block text-sm font-bold text-slate-700 mb-1">القطاع الكشفي <span class="text-rose-600">*</span></label>
                                    <select class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600" name="qetaa_id" id="qetaa_id" required>
                                        <option value="" disabled {{ empty($qetaa_id) ? 'selected' : '' }}>
                                            اختر القطاع الكشفي
                                        </option>
                                        @foreach ($available_qetaat as $qetaa)
                                            <option value="{{ $qetaa['QetaaID'] }}"
                                                {{ (string) ($qetaa_id ?? '') === (string) $qetaa['QetaaID'] ? 'selected' : '' }}>
                                                {{ $qetaa['QetaaName'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="error hidden mt-1 text-sm text-rose-600">هذا الحقل مطلوب</p>
                                    <label name="qetaa_name" id="qetaa_name" value="{{ $qetaa_name }}" hidden></label>
                                </div>
                            </div>
                        </section>

                        <div class="mt-8 flex justify-between gap-3">
                            <button type="button" @click="step = 2"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 py-3.5 font-bold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-600 transition">
                                → السابق
                            </button>
                            <button type="button" data-next-step="3"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-700 px-8 py-3.5 font-bold text-white shadow-md shadow-teal-900/10 hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 transition">
                                المراجعة ←
                            </button>
                        </div>
                    </div>

                    <!-- ============================ STEP 4: Review ============================ -->
                    <div data-step="4" x-show="step === 4" x-transition.opacity.duration.200ms>

                        <section class="rounded-2xl border border-teal-100 bg-white p-5 md:p-6">
                            <div class="mb-5">
                                <h2 class="text-xl font-bold text-brand-900">المراجعة</h2>
                                <p class="text-slate-500 mt-1 text-sm">راجع البيانات قبل الإرسال، ثم اضغط استمرار للانتقال إلى الأسئلة.</p>
                            </div>

                            <div id="reviewSummary" class="space-y-3 text-sm">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="rounded-xl bg-teal-50/60 border border-teal-100 px-4 py-3">
                                        <div class="text-xs text-slate-500 mb-1">الاسم الكامل</div>
                                        <div class="font-bold text-slate-900" data-review="full_name">—</div>
                                    </div>
                                    <div class="rounded-xl bg-teal-50/60 border border-teal-100 px-4 py-3">
                                        <div class="text-xs text-slate-500 mb-1">النوع</div>
                                        <div class="font-bold text-slate-900" data-review="gender">—</div>
                                    </div>
                                    <div class="rounded-xl bg-teal-50/60 border border-teal-100 px-4 py-3">
                                        <div class="text-xs text-slate-500 mb-1">تاريخ الميلاد</div>
                                        <div class="font-bold text-slate-900" data-review="birthdate">—</div>
                                    </div>
                                    <div class="rounded-xl bg-teal-50/60 border border-teal-100 px-4 py-3">
                                        <div class="text-xs text-slate-500 mb-1">فصيلة الدم</div>
                                        <div class="font-bold text-slate-900" data-review="blood">—</div>
                                    </div>
                                    <div class="rounded-xl bg-teal-50/60 border border-teal-100 px-4 py-3">
                                        <div class="text-xs text-slate-500 mb-1">موبايل الملتحق</div>
                                        <div class="font-bold text-slate-900" data-review="phone" dir="ltr">—</div>
                                    </div>
                                    <div class="rounded-xl bg-teal-50/60 border border-teal-100 px-4 py-3">
                                        <div class="text-xs text-slate-500 mb-1">الرقم القومي</div>
                                        <div class="font-bold text-slate-900" data-review="nid" dir="ltr">—</div>
                                    </div>
                                    <div class="rounded-xl bg-teal-50/60 border border-teal-100 px-4 py-3 sm:col-span-2">
                                        <div class="text-xs text-slate-500 mb-1">العنوان</div>
                                        <div class="font-bold text-slate-900" data-review="address">—</div>
                                    </div>
                                    <div class="rounded-xl bg-teal-50/60 border border-teal-100 px-4 py-3">
                                        <div class="text-xs text-slate-500 mb-1">المدرسة</div>
                                        <div class="font-bold text-slate-900" data-review="school">—</div>
                                    </div>
                                    <div class="rounded-xl bg-teal-50/60 border border-teal-100 px-4 py-3">
                                        <div class="text-xs text-slate-500 mb-1">القطاع الكشفي</div>
                                        <div class="font-bold text-slate-900" data-review="qetaa">—</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-amber-900">
                                <div class="font-bold mb-1">تنبيه مهم</div>
                                <div class="text-sm leading-relaxed">
                                    برجاء التأكد من البيانات مرة أخرى قبل ضغط "استمرار".
                                    سيتم الانتقال إلى باقي الأسئلة الخاصة بالقطاع بعد الضغط.
                                </div>
                            </div>
                        </section>

                        <div class="mt-8 flex justify-between gap-3">
                            <button type="button" @click="step = 3"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-6 py-3.5 font-bold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-600 transition">
                                → السابق
                            </button>
                            <button id="submitBtn" type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-700 px-10 py-3.5 font-bold text-white shadow-md shadow-teal-900/10 hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                <span>استمرار</span>
                                <span>←</span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <p class="mt-8 text-center text-xs text-slate-500">
            © {{ date('Y') }} مجموعة الشمندورة الكشفية البحرية — الإسكندرية
        </p>
    </div>


    <script>
        const form = document.getElementById('regForm2');
        const submitBtn = document.getElementById('submitBtn');
        const wizardRoot = document.getElementById('wizardRoot');

        const MAX_IMAGE_BYTES = 5 * 1024 * 1024;
        const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

        const touchedFields = new Set();
        const processedFiles = new Map();
        const processingState = new Map();
        const previewUrls = new WeakMap();

        function getWizardStep() {
            if (wizardRoot && window.Alpine) {
                try {
                    return Alpine.$data(wizardRoot).step;
                } catch (e) {}
            }
            return 1;
        }

        function setWizardStep(n) {
            if (wizardRoot && window.Alpine) {
                try {
                    Alpine.$data(wizardRoot).step = n;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                } catch (e) {}
            }
        }

        function stepOfElement(el) {
            const panel = el?.closest?.('[data-step]');
            return panel ? parseInt(panel.getAttribute('data-step'), 10) : null;
        }

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

        function sanitizeArabic(value) {
            return (value || '').replace(/[^\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]/g, '');
        }

        function isArabicOnly(value) {
            const v = (value || '').trim();
            if (!v) return true;
            return /^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s]+$/.test(v);
        }

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

        function validateNIDWithDOB(nid, dob) {
            if (!nid || nid.length !== 14) return false;
            if (!dob) return true;

            const birthDate = new Date(dob);
            const year = birthDate.getFullYear();
            const month = String(birthDate.getMonth() + 1).padStart(2, '0');
            const day = String(birthDate.getDate()).padStart(2, '0');
            const century = year >= 2000 ? '3' : '2';
            const shortYear = String(year).slice(-2);
            const expectedPrefix = century + shortYear + month + day;

            return nid.startsWith(expectedPrefix);
        }

        function validateNID(el) {
            el.value = onlyDigits(el.value).slice(0, 14);

            if (!el.value.trim()) return validateRequired(el);

            const basicValid = el.value.length === 14;
            const dobInput = document.getElementById('birthdate_input');
            const dob = dobInput ? dobInput.value : null;
            const matchesDOB = validateNIDWithDOB(el.value, dob);
            const ok = basicValid && matchesDOB;

            showError(el, !ok, 'nid');
            return ok;
        }

        function validateEmailField(el) {
            const cleaned = normalizeEmail(el.value);
            el.value = cleaned;

            if (!cleaned) {
                showEmailError(el, false);
                return true;
            }

            const ok = isValidEmail(cleaned);
            showEmailError(el, !ok);
            return ok;
        }

        function validateArabicName(el) {
            const cleaned = sanitizeArabic(el.value);
            if (el.value !== cleaned) el.value = cleaned;

            if (el.hasAttribute('required') && !el.value.trim()) {
                showError(el, true);
                return false;
            }

            const ok = isArabicOnly(el.value);
            showError(el, !ok);
            return ok;
        }

        function validateField(el) {
            if (!touchedFields.has(el.id)) return true;
            if (!el.classList.contains('field') && !el.classList.contains('field-email')) return true;

            if (el.id === 'personal_phone_number') return validatePhone(el);
            if (el.id === 'input_raqam_qawmy') return validateNID(el);
            if (el.id === 'email_input') return validateEmailField(el);

            if (['first_name', 'second_name', 'third_name', 'fourth_name'].includes(el.id)) {
                return validateArabicName(el);
            }

            return validateRequired(el);
        }

        const emergencyCheckbox = document.getElementById('has_emergency_case');
        const emergencyDetails = document.getElementById('emergency_details');
        const emergencyDetailsError = document.getElementById('emergency_details_error');

        const birthdateInput = document.getElementById('birthdate_input');
        if (birthdateInput) {
            birthdateInput.addEventListener('change', () => {
                const nidField = document.getElementById('input_raqam_qawmy');
                if (nidField && touchedFields.has(nidField.id)) {
                    validateNID(nidField);
                    validateAll();
                }
            });
        }

        function validateEmergencyDetails() {
            if (!emergencyCheckbox || !emergencyDetails) return true;

            const needs = emergencyCheckbox.checked;
            const val = (emergencyDetails.value || '').trim();

            if (needs && !val) {
                if (emergencyDetailsError) emergencyDetailsError.classList.remove('hidden');
                emergencyDetails.classList.add('border-rose-600', 'ring-2', 'ring-rose-200');
                emergencyDetails.classList.remove('border-slate-300');
                return false;
            }

            if (emergencyDetailsError) emergencyDetailsError.classList.add('hidden');
            emergencyDetails.classList.remove('border-rose-600', 'ring-2', 'ring-rose-200');
            emergencyDetails.classList.add('border-slate-300');
            return true;
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

        function formatFileSize(bytes) {
            if (!bytes) return '0 بايت';
            const k = 1024;
            const sizes = ['بايت', 'كيلو بايت', 'ميجا بايت'];
            const i = Math.min(Math.floor(Math.log(bytes) / Math.log(k)), sizes.length - 1);
            return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
        }

        function setProcessing(inputName, value) {
            processingState.set(inputName, value);
            updateSubmitState();
        }

        function isAnyFileProcessing() {
            return Array.from(processingState.values()).some(Boolean);
        }

        function updateSubmitState() {
            if (!submitBtn) return;
            if (isAnyFileProcessing()) {
                submitBtn.disabled = true;
                return;
            }
            submitBtn.disabled = false;
        }

        function getActiveFileForInput(input) {
            if (!input) return null;
            return processedFiles.get(input.name) || (input.files && input.files[0]) || null;
        }

        function clearPreviewUrl(img) {
            const oldUrl = previewUrls.get(img);
            if (oldUrl) {
                URL.revokeObjectURL(oldUrl);
                previewUrls.delete(img);
            }
        }

        function resetPhotoUI(root) {
            const input = root.querySelector('input[data-file]');
            const img = root.querySelector('[data-preview]');
            const placeholder = root.querySelector('[data-placeholder]');
            const filename = root.querySelector('[data-filename]');
            const errorKey = input?.getAttribute('data-error-key') || '';
            const err = errorKey ? root.querySelector(`[data-error="${errorKey}"]`) : null;

            if (input?.name) {
                processedFiles.delete(input.name);
                processingState.delete(input.name);
            }

            if (err) err.classList.add('hidden');

            if (img) {
                clearPreviewUrl(img);
                img.src = '';
                img.classList.add('hidden');
            }

            if (placeholder) placeholder.classList.remove('hidden');
            if (filename) filename.textContent = 'لم يتم اختيار ملف';

            updateSubmitState();
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
                if (img) {
                    clearPreviewUrl(img);
                    img.src = '';
                    img.classList.add('hidden');
                }
                if (placeholder) placeholder.classList.remove('hidden');
                return;
            }

            if (img) {
                clearPreviewUrl(img);
                const url = URL.createObjectURL(file);
                previewUrls.set(img, url);
                img.src = url;
                img.classList.remove('hidden');
            }
            if (placeholder) placeholder.classList.add('hidden');
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
            if (file.size <= maxBytes) {
                return {
                    ok: true,
                    file
                };
            }

            const targetMime = browserSupportsWebp() ? 'image/webp' : 'image/jpeg';
            const img = await loadImageFromFile(file);
            const maxDimList = [2048, 1600, 1280, 1024];
            const qualityList = [0.82, 0.75, 0.68, 0.60, 0.52, 0.45];

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

                for (const q of qualityList) {
                    const blob = await canvasToBlob(canvas, targetMime, q);
                    if (!blob) continue;

                    if (blob.size <= maxBytes) {
                        const ext = targetMime === 'image/webp' ? 'webp' : 'jpg';
                        const safeBase = (file.name || 'image').replace(/\.[^.]+$/, '');
                        const newName = `${safeBase}-compressed.${ext}`;

                        return {
                            ok: true,
                            file: new File([blob], newName, {
                                type: targetMime
                            })
                        };
                    }
                }
            }

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
                validateAll();
                return;
            }

            const originalFile = input.files[0];
            const key = input.name;
            processedFiles.delete(key);

            if (!ALLOWED_IMAGE_TYPES.includes(originalFile.type)) {
                showPhotoError(root, 'الصورة يجب أن تكون بصيغة JPG أو PNG أو WebP');
                input.value = '';
                resetPhotoUI(root);
                validateAll();
                return;
            }

            setProcessing(key, true);

            try {
                let finalFile = originalFile;

                if (originalFile.size > MAX_IMAGE_BYTES) {
                    showPhotoError(root, `جارٍ ضغط الصورة... (الحجم الحالي ${formatFileSize(originalFile.size)})`);

                    const result = await compressToUnder5MB(originalFile, MAX_IMAGE_BYTES);
                    if (!result.ok) {
                        showPhotoError(root,
                            `لم نتمكن من ضغط الصورة لأقل من 5 ميجا. الحجم الحالي: ${formatFileSize(originalFile.size)}. برجاء اختيار صورة أصغر.`
                        );
                        input.value = '';
                        resetPhotoUI(root);
                        validateAll();
                        return;
                    }

                    finalFile = result.file;
                    hidePhotoError(root);
                }

                processedFiles.set(key, finalFile);
                setPhotoPreview(root, finalFile);
            } catch (error) {
                console.error(error);
                showPhotoError(root, 'حدث خطأ أثناء معالجة الصورة. برجاء اختيار صورة أخرى.');
                input.value = '';
                resetPhotoUI(root);
            } finally {
                setProcessing(key, false);
                validateAll();
            }
        }

        function validateFilesBeforeSubmit() {
            let ok = true;

            document.querySelectorAll('[data-upload]').forEach(root => {
                const input = root.querySelector('input[data-file]');
                if (!input) return;

                const file = getActiveFileForInput(input);
                if (!file) return;

                if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
                    showPhotoError(root, 'الصورة يجب أن تكون بصيغة JPG أو PNG أو WebP');
                    ok = false;
                    return;
                }

                if (file.size > MAX_IMAGE_BYTES) {
                    showPhotoError(root,
                        `حجم الصورة ما زال أكبر من 5 ميجا (${formatFileSize(file.size)}). برجاء اختيار صورة أصغر.`
                    );
                    ok = false;
                }
            });

            return ok;
        }

        document.querySelectorAll('[data-upload]').forEach(root => {
            const pickBtn = root.querySelector('[data-pick]');
            const input = root.querySelector('input[data-file]');
            if (!pickBtn || !input) return;

            resetPhotoUI(root);

            pickBtn.addEventListener('click', () => input.click());
            input.addEventListener('change', async () => {
                await handlePhotoChange(root);
            });
        });

        function validateAll() {
            let ok = true;

            const fields = form.querySelectorAll('.field[required]');
            fields.forEach(el => {
                if (touchedFields.has(el.id) && !validateField(el)) ok = false;
            });

            const emailField = document.getElementById('email_input');
            if (emailField && touchedFields.has(emailField.id)) {
                if (!validateEmailField(emailField)) ok = false;
            }

            if (!validateEmergencyDetails()) ok = false;
            if (isAnyFileProcessing()) ok = false;
            if (!validateFilesBeforeSubmit()) ok = false;

            if (submitBtn) submitBtn.disabled = !ok;
            return ok;
        }

        function validateStep(stepNum) {
            const panel = form.querySelector(`[data-step="${stepNum}"]`);
            if (!panel) return true;

            let ok = true;
            const fields = panel.querySelectorAll('.field[required], .field-email');
            fields.forEach(el => {
                touchedFields.add(el.id);
                if (el.classList.contains('field-email')) {
                    if (el.value.trim() && !validateEmailField(el)) ok = false;
                } else if (!validateField(el)) {
                    ok = false;
                }
            });

            if (stepNum === 1 && !validateEmergencyDetails()) ok = false;

            if (!ok) {
                const firstInvalid = panel.querySelector('.ring-rose-200');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            return ok;
        }

        function selectText(sel) {
            if (!sel || !sel.options || sel.selectedIndex < 0) return '';
            return (sel.options[sel.selectedIndex]?.text || '').trim();
        }

        function refreshReviewSummary() {
            const set = (key, val) => {
                const el = document.querySelector(`[data-review="${key}"]`);
                if (el) el.textContent = val || '—';
            };

            const names = ['first_name', 'second_name', 'third_name', 'fourth_name']
                .map(id => (document.getElementById(id)?.value || '').trim())
                .filter(Boolean)
                .join(' ');

            const genderSel = document.getElementById('gender');
            const genderText = selectText(genderSel) || (genderSel?.value === 'Male' ? 'ذكر' : genderSel?.value === 'Female' ? 'أنثى' : '');

            const building = document.getElementById('building_number')?.value || '';
            const floor = document.getElementById('floor_number')?.value || '';
            const apt = document.getElementById('appartment_number')?.value || '';
            const street = document.getElementById('sub_street_name')?.value || '';
            const mainStreet = document.getElementById('main_street_name')?.value || '';
            const manteqa = selectText(document.getElementById('manteqa_id'));
            const district = selectText(document.getElementById('district_id'));
            const addressParts = [
                building && `عمارة ${building}`,
                floor && `دور ${floor}`,
                apt && `شقة ${apt}`,
                street,
                mainStreet && `قرب ${mainStreet}`,
                manteqa,
                district
            ].filter(Boolean);

            set('full_name', names);
            set('gender', genderText);
            set('birthdate', document.getElementById('birthdate_input')?.value || '');
            set('blood', selectText(document.getElementById('blood_type_input')));
            set('phone', document.getElementById('personal_phone_number')?.value || '');
            set('nid', document.getElementById('input_raqam_qawmy')?.value || '');
            set('address', addressParts.join(' — '));
            set('school', document.getElementById('person_school')?.value || '');
            set('qetaa', selectText(document.getElementById('qetaa_id')));
        }

        document.querySelectorAll('[data-next-step]').forEach(btn => {
            btn.addEventListener('click', () => {
                const from = parseInt(btn.getAttribute('data-next-step'), 10);
                if (!validateStep(from)) return;
                const next = from + 1;
                setWizardStep(next);
                if (next === 4) refreshReviewSummary();
            });
        });

        form.addEventListener('blur', (e) => {
            const el = e.target;
            if (!el.classList.contains('field') && !el.classList.contains('field-email')) return;

            touchedFields.add(el.id);
            validateField(el);
            validateAll();
        }, true);

        form.addEventListener('input', (e) => {
            const el = e.target;

            if (['first_name', 'second_name', 'third_name', 'fourth_name'].includes(el.id)) {
                const cleaned = sanitizeArabic(el.value);
                if (el.value !== cleaned) el.value = cleaned;
            }

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

        form.addEventListener('submit', (e) => {
            const allFields = form.querySelectorAll('.field[required], .field-email');
            allFields.forEach(el => touchedFields.add(el.id));

            let ok = true;

            const requiredFields = form.querySelectorAll('.field[required]');
            requiredFields.forEach(el => {
                if (!validateField(el)) ok = false;
            });

            const emailField = document.getElementById('email_input');
            if (emailField && emailField.value.trim() && !validateEmailField(emailField)) ok = false;

            if (!validateEmergencyDetails()) ok = false;

            if (isAnyFileProcessing()) {
                ok = false;
                alert('برجاء الانتظار حتى ينتهي تجهيز الصور.');
            }

            if (!validateFilesBeforeSubmit()) ok = false;

            if (!ok) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.ring-rose-200') || form.querySelector(
                    '.error-photo:not(.hidden)');
                if (firstInvalid) {
                    const step = stepOfElement(firstInvalid);
                    if (step) setWizardStep(step);
                    setTimeout(() => {
                        firstInvalid.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 50);
                }
                return;
            }

            document.querySelectorAll('input[data-file]').forEach(input => {
                const processed = processedFiles.get(input.name);
                if (!processed) return;

                const dt = new DataTransfer();
                dt.items.add(processed);
                input.files = dt.files;
            });
        });

        if (submitBtn) submitBtn.disabled = false;
        validateAll();
    </script>

</body>

</html>
