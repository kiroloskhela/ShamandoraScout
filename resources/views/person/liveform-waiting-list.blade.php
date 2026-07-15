<!DOCTYPE html>
@php($locale = app()->getLocale())
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ __('Shamandora Scout - Waiting list') }}</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: {{ $locale === 'ar' ? "'Cairo'" : "'Source Sans 3'" }}, sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 flex items-center justify-center px-4">

    <div class="w-full max-w-3xl">

        <!-- Card -->
        <div class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 overflow-hidden">

            <!-- Header -->
            <div class="px-6 py-10 border-b border-slate-200 bg-slate-50 text-center">

                <!-- Logo -->
                <img src="{{ asset('img/shamandora.png') }}" alt="{{ __('Shamandora') }}"
                    class="mx-auto h-24 w-24 object-contain mb-4" />

                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">{{ __('Waiting list') }}</h1>

                <p class="text-slate-500 mt-2">
                    {{ __('Your request was registered successfully') }}
                </p>
            </div>

            <!-- Content -->
            <div class="p-6 md:p-8 text-center">

                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-6 text-amber-900">

                    <div class="text-5xl mb-4">
                        ⏳
                    </div>

                    <div class="font-bold text-xl mb-3">
                        {{ __('You have been placed on the waiting list') }}
                    </div>

                    <div class="text-sm md:text-base leading-loose">
                        {{ __('Due to current capacity in the sector, your request was added to the waiting list until places become available.') }}
                    </div>

                    <div class="mt-4 text-sm md:text-base leading-loose">
                        {{ __('We will contact you as soon as a suitable place is available.') }}
                    </div>

                </div>

                <div class="my-6 h-px bg-slate-200"></div>

                <!-- Footer -->
                <div class="text-slate-600 text-sm">
                    <div>
                        Copyright &copy; Shamandora Scout 2024
                    </div>

                    <div class="mt-2 font-bold text-indigo-600 text-lg">{{ __('Shamandora Scout Group') }}</div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>
