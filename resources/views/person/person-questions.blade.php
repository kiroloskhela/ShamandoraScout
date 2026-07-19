<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>كشافة الشمندورة - ادخال بيانات</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/webp" href="{{ asset('img/shamandora.webp') }}">

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

    <div class="max-w-6xl mx-auto px-4">

        <!-- Outer Card -->
        <div class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 overflow-hidden">

            <!-- Header -->
            <div class="px-6 md:px-10 py-8 border-b border-slate-200 bg-slate-50">
                <div class="flex flex-col items-center justify-center gap-4 text-center">
                    <!-- Logo -->
                    <img src="{{ asset('img/shamandora.webp') }}" alt="Logo" class="h-20 w-20 object-contain" />

                    <!-- Title -->
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                            استكمال بيانات ملتحق جديد
                        </h1>
                        <p class="text-slate-500 mt-2">
                            الحقول المطلوبة عليها علامة <span class="font-bold text-rose-700">*</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-10">

                <form id="regForm" method="POST" action="{{ route('person.entry-questions-submit') }}" novalidate>
                    @csrf

                    <!-- Hidden Person ID -->
                    <input type="hidden" name="person_id" value="{{ $person->PersonID }}">

                    <!-- ============================ Info Section ============================ -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">بيانات الملتحق</h2>
                                <p class="text-slate-500 mt-1 text-sm">بيانات للعرض فقط.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                معلومات
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                            <!-- Code -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Code') }}</label>
                                <input type="text" readonly value="{{ $person->ShamandoraCode }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <!-- Sector -->
                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Sector') }}</label>
                                <input type="text" readonly value="{{ $person->QetaaName }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">كلمة السر</label>
                                <input type="text" readonly value="مخفية — أعد التعيين من إدارة كلمات المرور"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <!-- Names -->
                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('First name') }}</label>
                                <input type="text" readonly value="{{ $person->FirstName }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Second name') }}</label>
                                <input type="text" readonly value="{{ $person->SecondName }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Third name') }}</label>
                                <input type="text" readonly value="{{ $person->ThirdName }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-3">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">{{ __('Fourth name') }}</label>
                                <input type="text" readonly value="{{ $person->FourthName }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                        </div>
                    </section>

                    <div class="my-8 h-px bg-slate-200"></div>

                    <!-- ============================ Questions Section ============================ -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الأخير: الأسئلة الخاصة بكل قطاع</h2>
                                <p class="text-slate-500 mt-1 text-sm">أجب على الأسئلة التالية لإكمال التسجيل.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-rose-50 text-rose-800 px-4 py-2 text-sm font-semibold border border-rose-200">
                                أسئلة
                            </span>
                        </div>

                        @php
                            $shownQuestions = false;
                        @endphp

                        <div class="space-y-6">
                            @foreach ($questions as $question)
                                @if ($question->NotToBeShown == 0)
                                    @php $shownQuestions = true; @endphp

                                    <!-- Question Card -->
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:p-5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="text-slate-900 font-semibold leading-relaxed">
                                                {{ $question->QuestionText }}
                                                @if ($question->IsRequired == 1)
                                                    <span class="text-rose-700 font-bold ms-1">*</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-4">

                                            @if ($question->RequiredAnswerType == 'MultipleChoice')
                                                <select name="{{ $question->QuestionID }}"
                                                    id="q_{{ $question->QuestionID }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                    <option value="" disabled selected>اختر من الاجابات المتاحة
                                                    </option>
                                                    @foreach (explode('|', $question->MCAnswer) as $answer)
                                                        <option value="{{ $answer }}">{{ $answer }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif($question->RequiredAnswerType == 'OpenQuestion')
                                                <input type="text" name="{{ $question->QuestionID }}"
                                                    id="q_{{ $question->QuestionID }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                    placeholder="أدخل إجابتك هنا">
                                            @elseif($question->RequiredAnswerType == 'TrueOrFalse')
                                                <select name="{{ $question->QuestionID }}"
                                                    id="q_{{ $question->QuestionID }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                    <option value="" disabled selected>اختر نعم أم لا</option>
                                                    <option value="نعم">{{ __('Yes') }}</option>
                                                    <option value="لا">{{ __('No') }}</option>
                                                </select>
                                            @endif

                                            <!-- Helper text -->
                                            @if ($question->IsRequired == 1)
                                                <p class="mt-2 text-xs text-slate-500">هذا السؤال مطلوب</p>
                                            @else
                                                <p class="mt-2 text-xs text-slate-500">{{ __('Optional') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if ($shownQuestions == false)
                                <div
                                    class="rounded-2xl bg-amber-50 border border-amber-200 p-5 text-amber-900 text-center">
                                    لا يوجد أسئلة مختصة لهذا القطاع
                                </div>
                            @endif
                        </div>
                    </section>

                    <div class="my-8 h-px bg-slate-200"></div>

                    <!-- Submit -->
                    <div class="flex justify-end">
                        <button type="submit" id="submit-button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-700 px-10 py-3.5 font-bold text-white shadow hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <span>تأكيد</span>
                            <span>✓</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</body>

</html>
