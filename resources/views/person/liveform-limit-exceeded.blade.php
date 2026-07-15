<!DOCTYPE html>
@php($locale = app()->getLocale())
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ __('Shamandora Scout | Registration unavailable') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: {{ $locale === 'ar' ? "'Cairo'" : "'Source Sans 3'" }}, sans-serif;
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
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-100 text-slate-800">
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-3xl">

            <div class="rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200 overflow-hidden">

                <!-- Top -->
                <div class="relative px-6 sm:px-10 py-10 border-b border-slate-200 bg-slate-50">
                    <div class="flex flex-col items-center text-center">

                        <div
                            class="mb-5 h-28 w-28 rounded-full bg-white ring-4 ring-white shadow-md border border-slate-200 overflow-hidden">
                            <img src="{{ asset('img/shamandora.png') }}" class="h-full w-full object-contain p-3"
                                alt="{{ __('Shamandora') }}" />
                        </div>

                        <div
                            class="mb-4 inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-bold text-amber-800 ring-1 ring-amber-200">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                            {{ __('Registration is currently closed') }}
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                            {{ __('No places available currently') }}
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm sm:text-base leading-7 text-slate-600">
                            {{ __('Sorry, the maximum number of requests for this stage/sector has been reached. You can try later or contact administrators to learn when registration opens again.') }}
                        </p>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 sm:p-10">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-900">{{ __('Reason') }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-6">
                                {{ __('The available capacity for this category is full.') }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-slate-900">{{ __('What should I do now?') }}</h3>
                            <p class="mt-2 text-sm text-slate-600 leading-6">
                                {{ __('You can wait until registration opens again, or contact administrators for more information about the opening date or possible exceptions.') }}
                            </p>
                        </div>


                    </div>

                    <!-- Optional info box -->
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 mb-8">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 text-amber-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 1112 3a9 9 0 019 9z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-amber-900">{{ __('Important information') }}</h4>
                                <p class="mt-1 text-sm leading-6 text-amber-800">
                                    {{ __('Seeing this page means the system checked the maximum allowed for this category before completing registration.') }}
                                </p>
                            </div>
                        </div>

                    </div>



                    <!-- Footer -->
                    <div class="mt-10 pt-6 border-t border-slate-200 text-center">
                        <p class="text-xs text-slate-500">© Shamandora Scout</p>
                        <p class="text-sm font-bold text-indigo-700 mt-1">{{ __('Shamandora Scout Group') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
