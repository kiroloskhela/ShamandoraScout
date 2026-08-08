<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $sameAs = config('seo.same_as');
    $appBase = rtrim((string) config('app.url'), '/');
@endphp
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    @include('partials.seo-head', [
        'seoTitle' => __('Shamandora Scout — Official Egyptian Sea Scout group'),
        'seoDescription' => __(
            'Official website of Shamandora Scout (الشمندوره البحريه). Egyptian Sea Scout group — same organization as our Facebook and Instagram. Activities, camps, registration, and news.'
        ),
        'seoCanonical' => $appBase.'/',
        'seoUrl' => $appBase.'/',
    ])
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var dark = stored === 'dark';
                if (dark) document.documentElement.classList.add('dark');
                else document.documentElement.classList.remove('dark');
            } catch (e) {}
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-950/90">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/logo-square.png') }}" alt="{{ __('Shamandora Scout logo') }}"
                    class="h-11 w-11 rounded-xl object-cover shadow-sm" width="44" height="44">
                <div>
                    <p class="text-base font-bold tracking-tight">Shamandora Scout</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">الشمندوره البحريه</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('home') }}"
                        class="inline-flex h-10 items-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700">
                        {{ __('Open dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login-auth') }}"
                        class="inline-flex h-10 items-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700">
                        {{ __('Log in') }}
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-emerald-100/70 via-transparent to-teal-100/40 dark:from-emerald-950/40 dark:to-slate-950">
            </div>
            <div class="relative mx-auto max-w-5xl px-4 py-16 sm:py-24">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">
                    {{ __('Official website') }}
                </p>
                <h1 class="mt-3 max-w-3xl text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-5xl">
                    Shamandora Scout
                </h1>
                <p class="mt-2 text-xl font-semibold text-emerald-800 dark:text-emerald-200 sm:text-2xl">
                    الشمندوره البحريه
                </p>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-600 dark:text-slate-300 sm:text-lg">
                    {{ __('We are Shamandora Scout (also known as Shamandora Sea Scout / ShamandoraScout) — an Egyptian Sea Scout group. This website is the official online home of the same organization you find on Facebook and Instagram.') }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $sameAs['facebook'] }}" rel="noopener noreferrer me" target="_blank"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-emerald-700">
                        Facebook
                    </a>
                    <a href="{{ $sameAs['instagram'] }}" rel="noopener noreferrer me" target="_blank"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-emerald-700">
                        Instagram
                    </a>
                    <a href="{{ route('login-auth') }}"
                        class="inline-flex h-11 items-center rounded-xl bg-slate-900 px-4 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900">
                        {{ __('Member login') }}
                    </a>
                </div>
            </div>
        </section>

        <section class="border-t border-slate-200 bg-white py-12 dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto grid max-w-5xl gap-8 px-4 sm:grid-cols-3">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Also known as') }}</h2>
                    <ul class="mt-3 space-y-1 text-sm text-slate-800 dark:text-slate-200">
                        <li>Shamandora Sea Scout</li>
                        <li>ShamandoraScout</li>
                        <li>كشافة الشمندورة</li>
                        <li>الشمندوره البحريه</li>
                    </ul>
                </div>
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Official profiles') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li>
                            <a class="font-medium text-emerald-700 hover:underline dark:text-emerald-300"
                                href="{{ $sameAs['facebook'] }}" rel="noopener noreferrer me" target="_blank">
                                facebook.com/ShamandoraScout
                            </a>
                        </li>
                        <li>
                            <a class="font-medium text-emerald-700 hover:underline dark:text-emerald-300"
                                href="{{ $sameAs['instagram'] }}" rel="noopener noreferrer me" target="_blank">
                                instagram.com/shamandora_scout
                            </a>
                        </li>
                        <li>
                            <a class="font-medium text-emerald-700 hover:underline dark:text-emerald-300"
                                href="{{ $sameAs['youtube'] }}" rel="noopener noreferrer" target="_blank">
                                YouTube
                            </a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Mobile app') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li>
                            <a class="font-medium text-emerald-700 hover:underline dark:text-emerald-300"
                                href="{{ $sameAs['app_store'] }}" rel="noopener noreferrer" target="_blank">
                                App Store — Shamandora
                            </a>
                        </li>
                        <li>
                            <a class="font-medium text-emerald-700 hover:underline dark:text-emerald-300"
                                href="{{ $sameAs['play_store'] }}" rel="noopener noreferrer" target="_blank">
                                Google Play — Shamandora
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 py-8 dark:border-slate-800">
        <div class="mx-auto flex max-w-5xl flex-col gap-3 px-4 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} Shamandora Scout · الشمندوره البحريه</p>
            <p>
                <a href="{{ route('login-auth') }}" class="hover:text-emerald-700 dark:hover:text-emerald-300">{{ __('Log in') }}</a>
                <span class="mx-2" aria-hidden="true">·</span>
                <a href="{{ url('/feedback') }}" class="hover:text-emerald-700 dark:hover:text-emerald-300">{{ __('Feedback') }}</a>
            </p>
        </div>
    </footer>
</body>

</html>
