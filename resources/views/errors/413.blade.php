<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
@endphp
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Upload too large') }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('img/shamandora.webp') }}">
    @include('partials.fonts')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: {{ $locale === 'ar' ? "'Tajawal'" : "'Source Sans 3'" }}, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex items-center justify-center px-4 py-10">
    <main class="w-full max-w-lg rounded-2xl bg-white shadow-lg ring-1 ring-slate-200 p-8 text-center">
        <img src="{{ asset('img/shamandora.webp') }}" alt="" class="h-16 w-16 mx-auto mb-4 object-contain">
        <h1 class="text-2xl font-extrabold text-teal-800 mb-3">{{ __('Upload too large') }}</h1>
        <p class="text-slate-600 leading-relaxed mb-6">
            {{ __('The uploaded files are too large. Choose smaller photos (under 5 MB each) and try again.') }}
        </p>
        <a href="{{ url('/') }}"
            class="inline-flex items-center justify-center rounded-full bg-teal-700 text-white px-6 py-3 font-bold hover:bg-teal-800">
            {{ __('Go back') }}
        </a>
    </main>
</body>
</html>
