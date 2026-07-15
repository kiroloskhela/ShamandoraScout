<!DOCTYPE html>
@php($locale = app()->getLocale())
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ __('Shamandora Scout - New enrolment') }}</title>

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

                <!-- Logo (ONLY HERE) -->
                <img src="{{ asset('img/shamandora.png') }}" alt="{{ __('Shamandora') }}"
                    class="mx-auto h-24 w-24 object-contain mb-4" />

                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                    {{ __('Thank you') }}
                </h1>
                <p class="text-slate-500 mt-2">
                    {{ __('Enrolment application received successfully') }}
                </p>
            </div>

            <!-- Content -->
            <div class="p-6 md:p-8 text-center">

                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5 text-emerald-900">
                    <div class="font-bold text-lg mb-2">{{ __('Your application was submitted successfully') }} ✅</div>
                    <div class="text-sm leading-relaxed">
                        {{ __('A sector leader will contact you very soon.') }}
                    </div>
                </div>

                <div class="my-6 h-px bg-slate-200"></div>

                <!-- Footer -->
                <div class="text-slate-600 text-sm">
                    <div>Copyright &copy; Shamandora Scout 2024</div>
                    <div class="mt-2 font-bold text-indigo-600 text-lg">{{ __('Shamandora Scout Group') }}</div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>
