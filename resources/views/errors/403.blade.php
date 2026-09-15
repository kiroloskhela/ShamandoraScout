<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $safeHome = auth()->check() ? route('home') : route('login-auth');
    $backUrl = url()->previous($safeHome);
    $showBack = $backUrl !== url()->current() && $backUrl !== $safeHome;
@endphp
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Access denied | Shamandora Scout') }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('img/shamandora.webp') }}">
    @include('partials.fonts')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: {{ $locale === 'ar' ? "'Cairo'" : "'Source Sans 3'" }}, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex items-center justify-center px-4 py-10">
    <main class="w-full max-w-lg rounded-2xl bg-white shadow-lg ring-1 ring-slate-200 p-8 text-center">
        <img src="{{ asset('img/shamandora.webp') }}" alt="" class="h-16 w-16 mx-auto mb-4 object-contain">
        <h1 class="text-2xl font-extrabold text-teal-800 mb-3">{{ __('Access denied') }}</h1>
        <p class="text-slate-600 leading-relaxed mb-6">
            {{ __('You do not have permission to open this page.') }}
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            @auth
                <a href="{{ route('home') }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-teal-700 text-white px-6 py-3 font-bold hover:bg-teal-800">
                    {{ __('Dashboard') }}
                </a>
            @else
                <a href="{{ route('login-auth') }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-teal-700 text-white px-6 py-3 font-bold hover:bg-teal-800">
                    {{ __('Log in') }}
                </a>
            @endauth
            @if ($showBack)
                <a href="{{ $backUrl }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 px-6 py-3 font-bold hover:bg-slate-50">
                    {{ __('Go back') }}
                </a>
            @endif
        </div>
    </main>
</body>
</html>
