<!DOCTYPE html>
@php($locale = app()->getLocale())
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('Shamandora Scout - Complete information') }}</title>

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
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: {{ $locale === 'ar' ? "'Tajawal'" : "'Source Sans 3'" }}, sans-serif;
        }

        .sea-bg {
            background:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(13, 148, 136, 0.25), transparent),
                linear-gradient(165deg, #f0fdfa 0%, #ecfeff 40%, #f8fafc 100%);
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #99f6e4;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #5eead4;
        }

        input[type="text"],
        select {
            min-height: 50px;
        }

        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230f766e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: {{ $locale === 'ar' ? 'left' : 'right' }} 0.75rem center;
            background-size: 1.25rem;
            padding-{{ $locale === 'ar' ? 'left' : 'right' }}: 2.5rem;
            line-height: 1.5;
        }

        select option {
            text-align: {{ $locale === 'ar' ? 'right' : 'left' }};
            direction: {{ $locale === 'ar' ? 'rtl' : 'ltr' }};
        }
    </style>
</head>

<body class="sea-bg min-h-screen py-8">
    @php
        $isResumeMode = !empty($is_resume_mode);
        $existingAnswers = $existingAnswers ?? [];

        if ($existingAnswers instanceof \Illuminate\Support\Collection) {
            $existingAnswers = $existingAnswers->toArray();
        }

        $requestNumber =
            $isResumeMode && !empty($person->PersonID)
                ? $person->PersonID
                : __('Request number will be created after final confirmation');
    @endphp

    <div class="max-w-4xl mx-auto px-4">
        <div class="rounded-3xl bg-white/90 shadow-xl ring-1 ring-teal-100 overflow-hidden backdrop-blur">

            <div class="px-6 md:px-10 py-8 border-b border-teal-100 bg-gradient-to-b from-teal-50/80 to-white">
                <div class="flex flex-col items-center justify-center gap-4 text-center">
                    <img src="{{ asset('img/shamandora.png') }}" alt="{{ __('Shamandora') }}"
                        class="h-20 w-20 object-contain drop-shadow-md" />

                    <div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-brand-900">
                            {{ __('Complete your information') }}
                        </h1>
                        <p class="text-slate-600 mt-2 text-sm md:text-base">
                            {{ __('Required fields are marked') }} <span class="font-bold text-rose-600">**</span>
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

                    <section class="rounded-2xl border border-teal-100 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-brand-900">{{ __('Request details') }}</h2>
                                <p class="text-slate-500 mt-1 text-sm">{{ __('These details are for display only.') }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-teal-50 text-brand-800 px-4 py-2 text-sm font-bold ring-1 ring-teal-100">
                                {{ __('Info') }}
                            </span>
                        </div>

                        @if ($isResumeMode)
                            <div class="mb-5 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-amber-900">
                                <div class="font-bold mb-1">{{ __('Completing a previous request') }}</div>
                                <div class="text-sm leading-relaxed">
                                    {{ __('This page is for completing the remaining questions for this request.') }}
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-6">
                                <label class="block text-sm font-bold text-slate-700 mb-1">{{ __('Request number') }}</label>
                                <input type="text" readonly value="{{ $requestNumber }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-sm font-bold text-slate-700 mb-1">{{ __('Sector') }}</label>
                                <input type="text" readonly value="{{ $person->QetaaName ?? '' }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-sm font-bold text-slate-700 mb-3 text-center">{{ __('Name') }}</label>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                    <div class="md:col-span-3">
                                        <input type="text" readonly value="{{ $person->FirstName ?? '' }}"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                            placeholder="{{ __('First name') }}">
                                    </div>

                                    <div class="md:col-span-3">
                                        <input type="text" readonly value="{{ $person->SecondName ?? '' }}"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                            placeholder="{{ __('Second name') }}">
                                    </div>

                                    <div class="md:col-span-3">
                                        <input type="text" readonly value="{{ $person->ThirdName ?? '' }}"
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                            placeholder="{{ __('Third name') }}">
                                    </div>

                                    @if (!empty($person->FourthName))
                                        <div class="md:col-span-3">
                                            <input type="text" readonly value="{{ $person->FourthName }}"
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-slate-900 focus:outline-none"
                                                placeholder="{{ __('Fourth name') }}">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="my-8 h-px bg-teal-100"></div>

                    <section class="rounded-2xl border border-teal-100 bg-white p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <h2 class="text-xl font-bold text-brand-900">{{ __('Final part: Sector-specific questions') }}</h2>
                                <p class="text-slate-500 mt-1 text-sm">{{ __('Answer the following questions to complete the request.') }}</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full bg-rose-50 text-rose-800 px-4 py-2 text-sm font-bold border border-rose-200">
                                {{ __('Questions') }}
                            </span>
                        </div>

                        <div class="rounded-2xl bg-teal-50 border border-teal-100 p-4 text-brand-900 mb-6">
                            <div class="font-bold mb-1">{{ __('Note') }}</div>
                            <div class="text-sm leading-relaxed">
                                {{ __('Questions marked with (**) are required and must be answered to complete registration successfully.') }}
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

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 md:p-5">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="font-semibold text-slate-900 leading-relaxed text-{{ $locale === 'ar' ? 'right' : 'left' }}">
                                                @if ($question->IsRequired == 1)
                                                    <span class="text-rose-600 font-bold ms-2">**</span>
                                                @endif
                                                {{ $question->QuestionText }}
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            @if ($question->RequiredAnswerType == 'MultipleChoice')
                                                <select name="{{ $question->QuestionID }}"
                                                    id="{{ $question->QuestionID }}"
                                                    class="w-full md:w-1/2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                                    <option value="" disabled
                                                        {{ $selectedAnswer === '' ? 'selected' : '' }}>
                                                        {{ __('Choose from available answers') }}
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
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-600"
                                                    placeholder="{{ __('Enter your answer here') }}"
                                                    value="{{ $selectedAnswer }}">
                                            @elseif ($question->RequiredAnswerType == 'TrueOrFalse')
                                                <select name="{{ $question->QuestionID }}"
                                                    id="{{ $question->QuestionID }}"
                                                    class="w-full md:w-1/2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-600">
                                                    <option value="" disabled
                                                        {{ $selectedAnswer === '' ? 'selected' : '' }}>
                                                        {{ __('Choose yes or no') }}
                                                    </option>
                                                    <option value="نعم"
                                                        {{ $selectedAnswer == 'نعم' ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                                    <option value="لا"
                                                        {{ $selectedAnswer == 'لا' ? 'selected' : '' }}>{{ __('No') }}</option>
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
                                {{ __('No questions for this sector') }}
                            </div>
                        @endif
                    </section>

                    <div class="my-8 h-px bg-teal-100"></div>

                    <div class="flex justify-end">
                        <button type="submit" id="submit-button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-700 px-10 py-3.5 font-bold text-white shadow-md shadow-teal-900/10 hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600 transition">
                            <span>{{ $isResumeMode ? __('Save and complete request') : __('Confirm') }}</span>
                            <span>✓</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="mt-8 text-center text-xs text-slate-500">
            © {{ date('Y') }} {{ __('Sea Shamandora Scout Group — Alexandria') }}
        </p>
    </div>
</body>

</html>
