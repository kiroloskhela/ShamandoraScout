<!DOCTYPE html>
@php($locale = app()->getLocale())
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Shamandora Scout - New enrolment') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/shamandora.webp') }}">
    <style>
        body { font-family: {{ $locale === 'ar' ? "'Tajawal'" : "'Source Sans 3'" }}, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center px-4">
    <main class="w-full max-w-lg text-center">
        <img src="{{ asset('img/shamandora.webp') }}" alt="{{ __('Shamandora') }}" class="mx-auto h-24 w-24 object-contain">
        <div class="mt-8 rounded-3xl bg-white shadow-xl ring-1 ring-teal-100 px-8 py-10">
            <h1 class="text-2xl font-extrabold text-teal-900">{{ __('Please try again') }}</h1>
            <p class="mt-4 text-base leading-relaxed text-slate-700">
                {{ __('We could not open the form just now. Please try again. Saved answers stay on this device for one day.') }}
            </p>
            <a href="{{ route('person.liveform-create') }}"
                class="mt-6 inline-flex items-center justify-center rounded-2xl bg-teal-700 px-6 py-3 font-bold text-white hover:bg-teal-800">
                {{ __('Try again') }}
            </a>
        </div>
    </main>
</body>
</html>
