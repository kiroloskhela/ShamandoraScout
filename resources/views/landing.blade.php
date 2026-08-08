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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Source+Sans+3:wght@400;600;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sea: {
                            ink: '#061525',
                            deep: '#0a2740',
                            teal: '#0f766e',
                            foam: '#ecfeff',
                            mist: '#94a3b8',
                            sand: '#b08d57',
                        }
                    },
                    fontFamily: {
                        display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
                        arabic: ['Cairo', 'sans-serif'],
                        sans: ['"Source Sans 3"', 'Cairo', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        :root {
            --sea-ink: #061525;
            --sea-teal: #0f766e;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: {{ $isRtl ? "'Cairo'" : "'Source Sans 3'" }}, sans-serif;
            background: var(--sea-ink);
            color: #f8fafc;
        }

        .font-display {
            font-family: "Cormorant Garamond", Georgia, serif;
        }

        .font-arabic {
            font-family: Cairo, sans-serif;
        }

        .hero-wash {
            background:
                radial-gradient(ellipse 90% 70% at 70% 20%, rgba(15, 118, 110, 0.38), transparent 55%),
                radial-gradient(ellipse 60% 50% at 10% 80%, rgba(12, 74, 110, 0.45), transparent 50%),
                linear-gradient(165deg, #061525 0%, #0a2740 48%, #082f2b 100%);
        }

        .crest-glow {
            filter: drop-shadow(0 0 40px rgba(45, 212, 191, 0.25));
        }

        .wave-line {
            stroke-dasharray: 8 14;
            animation: wave-drift 18s linear infinite;
        }

        @keyframes wave-drift {
            to { stroke-dashoffset: -220; }
        }

        @keyframes rise-in {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes crest-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .anim-rise {
            animation: rise-in 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .anim-rise-delay-1 { animation-delay: 0.12s; }
        .anim-rise-delay-2 { animation-delay: 0.24s; }
        .anim-rise-delay-3 { animation-delay: 0.36s; }
        .anim-rise-delay-4 { animation-delay: 0.48s; }

        .anim-crest {
            animation: crest-float 7s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .wave-line,
            .anim-rise,
            .anim-crest {
                animation: none !important;
            }
        }

        .focus-ring:focus-visible {
            outline: 2px solid #5eead4;
            outline-offset: 3px;
        }
    </style>
</head>

<body class="min-h-screen antialiased">
    {{-- Skip link --}}
    <a href="#main"
        class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-teal-500 focus:px-4 focus:py-2 focus:text-sea-ink">
        {{ __('Skip to main content') }}
    </a>

    {{-- Full-bleed hero = brand composition --}}
    <header class="relative isolate min-h-[100svh] overflow-hidden hero-wash">
        {{-- Atmosphere: large crest as visual plane --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <img src="{{ asset('img/shamandora-white.webp') }}" alt=""
                class="anim-crest crest-glow absolute {{ $isRtl ? '-start-16' : '-end-16' }} top-16 h-[min(70vh,520px)] w-auto opacity-[0.12] sm:opacity-[0.16] lg:opacity-20">
            <div class="absolute inset-0 bg-gradient-to-t from-sea-ink via-transparent to-sea-ink/40"></div>
        </div>

        {{-- Top bar --}}
        <div class="relative z-10 mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 pt-6 sm:px-8">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/logo-square-white.webp') }}" alt="{{ __('Shamandora Scout logo') }}"
                    class="h-12 w-12 rounded-2xl object-cover ring-1 ring-white/20" width="48" height="48">
                <div class="leading-tight">
                    <p class="font-display text-lg font-semibold tracking-wide text-white sm:text-xl">Shamandora Scout</p>
                    <p class="font-arabic text-xs text-teal-200/80">الشمندوره البحريه</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <div class="hidden items-center rounded-full border border-white/15 bg-white/5 p-1 text-xs sm:flex"
                    role="group" aria-label="{{ __('Language') }}">
                    <a href="{{ url('/locale/ar') }}"
                        class="focus-ring rounded-full px-3 py-1.5 font-semibold transition {{ $isRtl ? 'bg-teal-500 text-sea-ink' : 'text-white/70 hover:text-white' }}">عربي</a>
                    <a href="{{ url('/locale/en') }}"
                        class="focus-ring rounded-full px-3 py-1.5 font-semibold transition {{ ! $isRtl ? 'bg-teal-500 text-sea-ink' : 'text-white/70 hover:text-white' }}">EN</a>
                </div>
                @auth
                    <a href="{{ route('home') }}"
                        class="focus-ring inline-flex h-11 items-center rounded-full bg-teal-500 px-5 text-sm font-bold text-sea-ink transition hover:bg-teal-300">
                        {{ __('Open dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login-auth') }}"
                        class="focus-ring inline-flex h-11 items-center rounded-full bg-teal-500 px-5 text-sm font-bold text-sea-ink transition hover:bg-teal-300">
                        {{ __('Log in') }}
                    </a>
                @endauth
            </div>
        </div>

        <main id="main" class="relative z-10 mx-auto flex max-w-6xl flex-col justify-center px-5 pb-28 pt-16 sm:px-8 sm:pb-32 sm:pt-24 lg:min-h-[calc(100svh-5.5rem)]">
            <div class="max-w-3xl">
                <p class="anim-rise font-arabic text-sm font-semibold tracking-[0.22em] text-teal-300/90 sm:text-base">
                    {{ __('Official website') }}
                </p>

                <h1 class="anim-rise anim-rise-delay-1 mt-4 font-display text-[clamp(2.75rem,8vw,5.5rem)] font-bold leading-[0.95] tracking-tight text-white">
                    Shamandora Scout
                </h1>

                <p class="anim-rise anim-rise-delay-2 mt-3 font-arabic text-2xl font-bold text-teal-200 sm:text-3xl">
                    الشمندوره البحريه
                </p>

                <p class="anim-rise anim-rise-delay-3 mt-6 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg {{ $isRtl ? 'font-arabic' : '' }}">
                    {{ __('We are Shamandora Scout (also known as Shamandora Sea Scout / ShamandoraScout) — an Egyptian Sea Scout group. This website is the official online home of the same organization you find on Facebook and Instagram.') }}
                </p>

                <div class="anim-rise anim-rise-delay-4 mt-10 flex flex-wrap items-center gap-3">
                    @auth
                        <a href="{{ route('home') }}"
                            class="focus-ring inline-flex h-12 items-center rounded-full bg-white px-6 text-sm font-bold text-sea-ink transition hover:bg-teal-100">
                            {{ __('Open dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login-auth') }}"
                            class="focus-ring inline-flex h-12 items-center rounded-full bg-white px-6 text-sm font-bold text-sea-ink transition hover:bg-teal-100">
                            {{ __('Member login') }}
                        </a>
                    @endauth

                    <a href="{{ $sameAs['facebook'] }}" rel="noopener noreferrer me" target="_blank"
                        class="focus-ring inline-flex h-12 items-center gap-2 rounded-full border border-white/25 bg-white/5 px-5 text-sm font-semibold text-white backdrop-blur transition hover:border-teal-300/60 hover:bg-white/10">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 13.5h2.5l.5-3H14v-1.8c0-.9.2-1.5 1.6-1.5H17V4.1C16.5 4 15.5 4 14.4 4 11.8 4 10 5.6 10 8.5V10.5H7.5v3H10V20h4v-6.5z"/></svg>
                        Facebook
                    </a>
                    <a href="{{ $sameAs['instagram'] }}" rel="noopener noreferrer me" target="_blank"
                        class="focus-ring inline-flex h-12 items-center gap-2 rounded-full border border-white/25 bg-white/5 px-5 text-sm font-semibold text-white backdrop-blur transition hover:border-teal-300/60 hover:bg-white/10">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 5a5 5 0 100 10 5 5 0 000-10zm6.5-.9a1.1 1.1 0 10-2.2 0 1.1 1.1 0 002.2 0zM12 9a3 3 0 110 6 3 3 0 010-6z"/></svg>
                        Instagram
                    </a>
                </div>
            </div>
        </main>

        {{-- Wave divider --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0 text-sea-foam" aria-hidden="true">
            <svg class="h-16 w-full sm:h-24" viewBox="0 0 1440 120" preserveAspectRatio="none" fill="none">
                <path class="wave-line" d="M0 60 C 180 20, 360 100, 540 60 S 900 20, 1080 60 S 1350 100, 1440 60"
                    stroke="rgba(94,234,212,0.35)" stroke-width="2" fill="none" />
                <path d="M0 80 C 240 40, 480 110, 720 70 S 1200 40, 1440 80 L1440 120 L0 120 Z"
                    fill="#f8fafc" opacity="0.97" />
            </svg>
        </div>
    </header>

    {{-- Identity section (one job: prove same org across the web) --}}
    <section class="relative overflow-hidden bg-slate-50 text-sea-ink" aria-labelledby="identity-heading">
        <div class="pointer-events-none absolute -end-24 top-10 h-72 w-72 rounded-full bg-teal-200/30 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -start-16 bottom-0 h-56 w-56 rounded-full bg-cyan-100/50 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
            <div class="grid items-start gap-10 lg:grid-cols-12 lg:gap-14">
                <div class="lg:col-span-5">
                    <div class="flex items-start gap-5">
                        <img src="{{ asset('img/logo-square.png') }}" alt="{{ __('Shamandora Scout logo') }}"
                            class="h-16 w-16 shrink-0 rounded-2xl object-cover shadow-lg shadow-teal-900/10 ring-1 ring-slate-200 sm:h-20 sm:w-20"
                            width="80" height="80">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-sea-teal">
                                {{ __('Same organization') }}
                            </p>
                            <h2 id="identity-heading" class="mt-2 font-display text-3xl font-bold tracking-tight text-sea-ink sm:text-4xl">
                                {{ __('One organization, everywhere') }}
                            </h2>
                        </div>
                    </div>

                    <p class="mt-6 text-base leading-relaxed text-slate-600 sm:text-lg {{ $isRtl ? 'font-arabic' : '' }}">
                        {{ __('If you follow Shamandora Scout on social media or use the Shamandora app, you are looking at the same Egyptian Sea Scout group as this website.') }}
                    </p>

                    <div class="mt-8">
                        <h3 class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">{{ __('Also known as') }}</h3>
                        <ul class="mt-4 flex flex-wrap gap-2" role="list">
                            <li class="rounded-full border border-slate-200 bg-white px-3.5 py-1.5 font-display text-sm font-semibold text-sea-ink shadow-sm">
                                Shamandora Sea Scout
                            </li>
                            <li class="rounded-full border border-slate-200 bg-white px-3.5 py-1.5 font-display text-sm font-semibold text-sea-ink shadow-sm">
                                ShamandoraScout
                            </li>
                            <li class="rounded-full border border-slate-200 bg-white px-3.5 py-1.5 font-arabic text-sm font-bold text-sea-ink shadow-sm">
                                كشافة الشمندورة
                            </li>
                            <li class="rounded-full border border-teal-200 bg-teal-50 px-3.5 py-1.5 font-arabic text-sm font-bold text-teal-900 shadow-sm">
                                الشمندوره البحريه
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <h3 class="text-xs font-bold uppercase tracking-[0.18em] text-sea-teal">{{ __('Official profiles') }}</h3>
                    <p class="mt-2 text-sm text-slate-500 {{ $isRtl ? 'font-arabic' : '' }}">
                        {{ __('These are the verified public profiles of Shamandora Scout — the same group as this site.') }}
                    </p>

                    <ul class="mt-5 space-y-3" role="list">
                        <li>
                            <a href="{{ $sameAs['facebook'] }}" rel="noopener noreferrer me" target="_blank"
                                class="focus-ring group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md sm:px-5">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#1877F2] text-white shadow-sm" aria-hidden="true">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M14 13.5h2.5l.5-3H14v-1.8c0-.9.2-1.5 1.6-1.5H17V4.1C16.5 4 15.5 4 14.4 4 11.8 4 10 5.6 10 8.5V10.5H7.5v3H10V20h4v-6.5z"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold text-sea-ink">Facebook</span>
                                    <span class="mt-0.5 block truncate text-sm text-slate-500">facebook.com/ShamandoraScout</span>
                                </span>
                                <span class="shrink-0 text-xs font-bold uppercase tracking-wide text-sea-teal opacity-0 transition group-hover:opacity-100">
                                    {{ __('Open') }}
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ $sameAs['instagram'] }}" rel="noopener noreferrer me" target="_blank"
                                class="focus-ring group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md sm:px-5">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#f58529] via-[#dd2a7b] to-[#8134af] text-white shadow-sm" aria-hidden="true">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 5a5 5 0 100 10 5 5 0 000-10zm6.5-.9a1.1 1.1 0 10-2.2 0 1.1 1.1 0 002.2 0zM12 9a3 3 0 110 6 3 3 0 010-6z"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold text-sea-ink">Instagram</span>
                                    <span class="mt-0.5 block truncate text-sm text-slate-500">instagram.com/shamandora_scout</span>
                                </span>
                                <span class="shrink-0 text-xs font-bold uppercase tracking-wide text-sea-teal opacity-0 transition group-hover:opacity-100">
                                    {{ __('Open') }}
                                </span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ $sameAs['youtube'] }}" rel="noopener noreferrer" target="_blank"
                                class="focus-ring group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md sm:px-5">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#FF0000] text-white shadow-sm" aria-hidden="true">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2a3 3 0 00-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 00.5 6.2 31.5 31.5 0 000 12a31.5 31.5 0 00.5 5.8 3 3 0 002.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 002.1-2.1A31.5 31.5 0 0024 12a31.5 31.5 0 00-.5-5.8zM9.8 15.5v-7l6.2 3.5-6.2 3.5z"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-bold text-sea-ink">YouTube</span>
                                    <span class="mt-0.5 block truncate text-sm text-slate-500">{{ __('Shamandora Scout channel') }}</span>
                                </span>
                                <span class="shrink-0 text-xs font-bold uppercase tracking-wide text-sea-teal opacity-0 transition group-hover:opacity-100">
                                    {{ __('Open') }}
                                </span>
                            </a>
                        </li>
                    </ul>

                    <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white/70 px-5 py-5">
                        <h3 class="text-xs font-bold uppercase tracking-[0.18em] text-sea-teal">{{ __('Mobile app') }}</h3>
                        <p class="mt-2 text-sm text-slate-600 {{ $isRtl ? 'font-arabic' : '' }}">
                            {{ __('The Shamandora mobile app is published by the same organization.') }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ $sameAs['app_store'] }}" rel="noopener noreferrer" target="_blank"
                                class="focus-ring inline-flex h-11 items-center rounded-full bg-sea-ink px-4 text-sm font-semibold text-white transition hover:bg-sea-deep">
                                App Store
                            </a>
                            <a href="{{ $sameAs['play_store'] }}" rel="noopener noreferrer" target="_blank"
                                class="focus-ring inline-flex h-11 items-center rounded-full border border-slate-300 bg-white px-4 text-sm font-semibold text-sea-ink transition hover:border-teal-400 hover:text-sea-teal">
                                Google Play
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Closing band --}}
    <section class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-5 py-14 sm:flex-row sm:items-center sm:px-8">
            <div class="max-w-xl">
                <p class="font-display text-2xl font-bold text-sea-ink sm:text-3xl">
                    {{ __('Ready to continue as a member?') }}
                </p>
                <p class="mt-2 text-sm text-slate-600 {{ $isRtl ? 'font-arabic' : '' }}">
                    {{ __('Use your Shamandora Scout account to open the portal.') }}
                </p>
            </div>
            <a href="{{ route('login-auth') }}"
                class="focus-ring inline-flex h-12 shrink-0 items-center rounded-full bg-sea-ink px-6 text-sm font-bold text-white transition hover:bg-sea-deep">
                {{ __('Log in') }}
            </a>
        </div>
    </section>

    <footer class="border-t border-white/10 bg-sea-ink">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-5 py-8 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <p>
                <span class="font-display text-base text-white">Shamandora Scout</span>
                <span class="mx-2 text-white/30">·</span>
                <span class="font-arabic">الشمندوره البحريه</span>
                <span class="mx-2 text-white/30">·</span>
                © {{ date('Y') }}
            </p>
            <nav class="flex flex-wrap items-center gap-x-4 gap-y-2" aria-label="{{ __('Footer') }}">
                <a class="focus-ring hover:text-teal-300" href="{{ route('login-auth') }}">{{ __('Log in') }}</a>
                <a class="focus-ring hover:text-teal-300" href="{{ url('/feedback') }}">{{ __('Feedback') }}</a>
                <a class="focus-ring hover:text-teal-300" href="{{ url('/locale/ar') }}">عربي</a>
                <a class="focus-ring hover:text-teal-300" href="{{ url('/locale/en') }}">EN</a>
            </nav>
        </div>
    </footer>
</body>

</html>
