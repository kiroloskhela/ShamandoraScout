<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>كشافة الشمندورة - استكمال البيانات</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        input[type="text"],
        select {
            min-height: 50px;
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
            line-height: 1.5;
        }

        select option {
            text-align: right;
            direction: rtl;
        }
    </style>
</head>

<body class="min-h-screen bg-white py-8">
    @php
        $isResumeMode = !empty($is_resume_mode);
        $existingAnswers = $existingAnswers ?? [];

        if ($existingAnswers instanceof \Illuminate\Support\Collection) {
            $existingAnswers = $existingAnswers->toArray();
        }

        $requestNumber =
            $isResumeMode && !empty($person->PersonID) ? $person->PersonID : 'سيتم إنشاء رقم الطلب بعد التأكيد النهائي';
    @endphp

    <div class="max-w-6xl mx-auto px-4">
        <div class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 overflow-hidden">

            <div class="px-6 md:px-10 py-8 border-b border-slate-200 bg-slate-50">
                <div class="flex flex-col items-center justify-center gap-4 text-center">
                    <img src="{{ asset('img/shamandora.png') }}" alt="Logo" class="h-20 w-20 object-contain" />

                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                            استكمال البيانات
                        </h1>
                        <p class="text-slate-500 mt-2">
                            الحقول المطلوبة عليها علامة <span class="font-bold text-rose-700">**</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-10">
                <form id="regForm" method="POST"
                    action="{{ $isResumeMode
                        ? route('person.liveform-resume-questions-submit', $person->PersonID)
                        : route('person.entry-questions-submit-liveform') }}"
                    novalidate>
                    @csrf

                    @if ($isResumeMode)
                        <input type="hidden" name="person_id" id="person_id" value="{{ $person->PersonID }}">
                    @endif

                    <input type="hidden" name="qetaa_id" id="qetaa_id" value="{{ $person->QetaaID }}">

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">بيانات الطلب</h2>
                                <p class="text-slate-500 mt-1 text-sm">هذه البيانات للعرض فقط.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-semibold">
                                معلومات
                            </span>
                        </div>

                        @if ($isResumeMode)
                            <div class="mb-5 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-amber-900">
                                <div class="font-bold mb-1">استكمال طلب سابق</div>
                                <div class="text-sm leading-relaxed">
                                    هذه الصفحة مخصصة لاستكمال الأسئلة المتبقية لهذا الطلب.
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">رقم الطلب</label>
                                <input type="text" readonly value="{{ $requestNumber }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-semibold text-slate-700 mb-1">القطاع</label>
                                <input type="text" readonly value="{{ $person->QetaaName ?? '' }}"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-sm font-semibold text-slate-700 mb-3 text-center">الاسم</label>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <div class="md:col-span-3">
                                        <input type="text" readonly value="{{ $person->FirstName ?? '' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                            placeholder="الاسم الأول">
                                    </div>

                                    <div class="md:col-span-3">
                                        <input type="text" readonly value="{{ $person->SecondName ?? '' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                            placeholder="الاسم الثاني">
                                    </div>

                                    <div class="md:col-span-3">
                                        <input type="text" readonly value="{{ $person->ThirdName ?? '' }}"
                                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                            placeholder="الاسم الثالث">
                                    </div>

                                    @if (!empty($person->FourthName))
                                        <div class="md:col-span-3">
                                            <input type="text" readonly value="{{ $person->FourthName }}"
                                                class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                                placeholder="الاسم الرابع">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="my-8 h-px bg-slate-200"></div>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">الجزء الأخير: الأسئلة الخاصة بكل قطاع</h2>
                                <p class="text-slate-500 mt-1 text-sm">أجب على الأسئلة التالية لاستكمال الطلب.</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-rose-50 text-rose-800 px-4 py-2 text-sm font-semibold border border-rose-200">
                                أسئلة
                            </span>
                        </div>

                        <div class="rounded-2xl bg-indigo-50 border border-indigo-200 p-4 text-indigo-900 mb-6">
                            <div class="font-bold mb-1">ملحوظة</div>
                            <div class="text-sm leading-relaxed">
                                الأسئلة التي يتبعها العلامة <span class="font-bold">(**)</span> هي أسئلة إجبارية ويجب
                                الإجابة عليها لاستكمال طلب التسجيل بنجاح.
                            </div>
                        </div>

                        @php
                            $noQuestionsFlag = true;
                        @endphp

                        <div class="space-y-6">
                            @foreach ($questions as $question)
                                @if ($question->NotToBeShown == 0)
                                    @php
                                        $noQuestionsFlag = false;
                                        $selectedAnswer = old(
                                            (string) $question->QuestionID,
                                            $existingAnswers[$question->QuestionID] ?? '',
                                        );
                                        $multiChoices =
                                            $question->RequiredAnswerType == 'MultipleChoice'
                                                ? array_filter(
                                                    array_map('trim', explode('|', (string) $question->MCAnswer)),
                                                    fn($value) => $value !== '',
                                                )
                                                : [];
                                    @endphp

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:p-5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="font-semibold text-slate-900 leading-relaxed text-right">
                                                @if ($question->IsRequired == 1)
                                                    <span class="text-rose-700 font-bold ms-2">**</span>
                                                @endif
                                                {{ $question->QuestionText }}
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            @if ($question->RequiredAnswerType == 'MultipleChoice')
                                                <select name="{{ $question->QuestionID }}"
                                                    id="{{ $question->QuestionID }}"
                                                    class="w-full md:w-1/2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                    <option value="" disabled
                                                        {{ $selectedAnswer === '' ? 'selected' : '' }}>
                                                        اختر من الاجابات المتاحة
                                                    </option>
                                                    @foreach ($multiChoices as $answer)
                                                        <option value="{{ $answer }}"
                                                            {{ $selectedAnswer == $answer ? 'selected' : '' }}>
                                                            {{ $answer }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif ($question->RequiredAnswerType == 'OpenQuestion')
                                                <input type="text" name="{{ $question->QuestionID }}"
                                                    id="{{ $question->QuestionID }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                    placeholder="أدخل إجابة السؤال هنا"
                                                    value="{{ $selectedAnswer }}">
                                            @elseif ($question->RequiredAnswerType == 'TrueOrFalse')
                                                <select name="{{ $question->QuestionID }}"
                                                    id="{{ $question->QuestionID }}"
                                                    class="w-full md:w-1/2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                    <option value="" disabled
                                                        {{ $selectedAnswer === '' ? 'selected' : '' }}>
                                                        اختر نعم أم لا
                                                    </option>
                                                    <option value="نعم"
                                                        {{ $selectedAnswer == 'نعم' ? 'selected' : '' }}>نعم</option>
                                                    <option value="لا"
                                                        {{ $selectedAnswer == 'لا' ? 'selected' : '' }}>لا</option>
                                                </select>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if ($noQuestionsFlag)
                            <div
                                class="mt-6 rounded-2xl bg-amber-50 border border-amber-200 p-5 text-amber-900 text-center">
                                لا يوجد أسئلة مختصة لهذا القطاع
                            </div>
                        @endif
                    </section>

                    <div class="my-8 h-px bg-slate-200"></div>

                    <div class="flex justify-end">
                        <button type="submit" id="submit-button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-700 px-10 py-3.5 font-bold text-white shadow hover:bg-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <span>{{ $isResumeMode ? 'حفظ واستكمال الطلب' : 'تأكيد' }}</span>
                            <span>✓</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
