<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $dir = $isRtl ? 'rtl' : 'ltr';
@endphp
<html lang="{{ $locale }}" dir="{{ $dir }}" class="bg-gray-50 dark:bg-slate-950">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('Shamandora Scout'))</title>
    <meta name="description" content="@yield('meta_description', __('Official Shamandora Scout site. Follow activities, events, registration, and news.'))">
    <meta name="keywords"
        content="الشمندوره البحريه, Shamandora Scout, scouts, sea scout, shamandora, الكشافة, الكشفية البحرية">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Shamandora Scout">
    <meta name="color-scheme" content="light dark">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Favicon / Logo in browser tab and Google icon --}}
    <link rel="icon" type="image/webp" href="{{ asset('img/shamandora.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/shamandora.png') }}">

    {{-- Open Graph for Facebook / WhatsApp / social preview --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', __('Shamandora Scout'))">
    <meta property="og:description" content="@yield('meta_description', __('Official Shamandora Scout site. Follow activities, events, registration, and news.'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('img/shamandora.webp') }}">
    <meta property="og:site_name" content="Shamandora Scout">

    {{-- Twitter / X preview --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', __('Shamandora Scout'))">
    <meta name="twitter:description" content="@yield('meta_description', __('Official Shamandora Scout site. Follow activities, events, registration, and news.'))">
    <meta name="twitter:image" content="{{ asset('img/shamandora.webp') }}">

    {{-- Prevent theme flash before CSS loads --}}
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                // Default to light unless the user explicitly chose dark
                var dark = stored === 'dark';
                if (dark) document.documentElement.classList.add('dark');
                else document.documentElement.classList.remove('dark');
            } catch (e) {}
        })();
    </script>

    {{-- Google Organization structured data for logo --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Shamandora Scout",
        "alternateName": "الشمندوره البحريه",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('img/shamandora.webp') }}",
        "sameAs": [
            "https://www.facebook.com/ShamandoraScout",
            "https://www.instagram.com/shamandora_scout"
        ]
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: {{ $isRtl ? "'Cairo'" : "'Source Sans 3'" }}, sans-serif;
        }
    </style>
    @include('partials.motion-styles')

    @stack('styles')
</head>

<body class="bg-gray-50 dark:bg-slate-950 min-h-screen">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:{{ $isRtl ? 'right-3' : 'left-3' }} focus:z-[100] focus:px-4 focus:py-2 focus:rounded-lg focus:bg-blue-600 focus:text-white focus:shadow-lg">
        {{ __('Skip to main content') }}
    </a>
    <!-- Main Layout Container -->
    <div class="flex flex-col min-h-screen bg-gray-50 dark:bg-slate-950">
        <!-- Main Wrapper -->
        <div class="flex flex-1 flex-col md:flex-row bg-gray-50 dark:bg-slate-950 min-h-0">
            <!-- Sidebar Overlay (Mobile) -->
            <div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black/50 dark:bg-black/70 backdrop-blur-[2px] lg:hidden hidden"></div>

            <!-- Sidebar Navigation -->
            <aside id="sidebar"
                data-dir="{{ $dir }}"
                class="fixed inset-y-0 z-50 w-80 max-w-[85vw] bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-700/80 shadow-xl transition-transform duration-300 flex flex-col overflow-hidden lg:translate-x-0 lg:shadow-none lg:w-72 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 {{ $isRtl ? 'right-0 border-l translate-x-full' : 'left-0 border-r -translate-x-full' }}">
                <!-- Mobile Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-slate-800 lg:hidden shrink-0">
                    <h2 class="text-lg font-semibold text-gray-800">{{ __('Menu') }}</h2>
                    <button id="closeSidebar"
                        class="p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- User Profile Section -->
                <div class="flex flex-col items-center p-6 border-b border-gray-200 dark:border-slate-800 shrink-0 bg-gradient-to-b from-transparent to-transparent dark:from-teal-950/30 dark:to-transparent">
                    <div class=" relative mb-3">
                        <img src="{{ Auth::user()->avatar_url }}" alt="User Avatar"
                            class="w-16 h-16 rounded-full border-2 border-white dark:border-slate-700 shadow-sm ring-2 ring-emerald-500/20 dark:ring-emerald-400/30">

                        <span
                            class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full shadow-[0_0_0_2px_rgba(52,211,153,0.25)]"></span>
                    </div>
                    <div class="text-center">
                        <h4 class="font-medium text-gray-800">{{ Auth::user()->FirstName ?? '' }}
                            {{ Auth::user()->SecondName ?? '' }}
                        </h4>
                        <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->ShamandoraCode ?? '' }}</p>
                    </div>
                </div>


                <!-- Navigation Menu -->
                <nav class="flex-1 min-h-0 overflow-y-auto overscroll-contain py-4">

                    @php
                        $isSuperAdmin = $isSuperAdmin ?? false;
                        $isSecretary = $isSecretary ?? false;
                        $isMedia = $isMedia ?? false;
                        $isInventory = $isInventory ?? false;
                        $isFinance = $isFinance ?? false;
                        $isAdminQetaa = $isAdminQetaa ?? false;
                        $isAdminSecretary = $isAdminSecretary ?? false;
                        $isAdminInventory = $isAdminInventory ?? false;
                        $isAdminFinance = $isAdminFinance ?? false;
                        $isAdminFirstAid = $isAdminFirstAid ?? false;
                    @endphp

                    {{-- ===================== SuperAdmin: System Constants ===================== --}}
                    @if ($isSuperAdmin)
                        <div class="px-3 mb-2">
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                    <span class="font-medium">{{ __('System constants') }}</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('rotab.index') }}">{{ __('Scout ranks') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('blood.index') }}">{{ __('Blood types') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('marhala.index') }}">{{ __('Academic stages') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('qetaa.index') }}">{{ __('Scout sectors') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('sana-marhala.index') }}">{{ __('Years & academic stages') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('entry-questions.index') }}">{{ __('Entry form questions') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('district.index') }}">{{ __('Residential districts') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('manteqa.index') }}">{{ __('Residential areas') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('faculty.index') }}">{{ __('Faculties') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('university.index') }}">{{ __('Universities') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('role.index') }}">{{ __('Roles & duties') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('person-role.index') }}">{{ __('Link roles & duties') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('group-type.index') }}">{{ __('Group types') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('group.index') }}">{{ __('Link groups') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('event-type.index') }}">{{ __('Event types') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('event.index') }}">{{ __('Scout events') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('group-person.index') }}">{{ __('Link people to groups') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('season.index') }}">{{ __('Manage seasons') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('season-event.index') }}">{{ __('Link season to event') }}</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ===================== Federations (visible to any logged-in user) ===================== --}}
                    @if ($isSuperAdmin || $isAdminQetaa || $isAdminSecretary || $isSecretary)
                        <div class="px-3 mb-2">
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                    <span class="font-medium">{{ __('Enrolments') }}</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">

                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ url('/liveform') }}">{{ __('LIVE registration form') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ url('/new-enrolments') }}">{{ __('Review enrolment requests') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ url('/persons/waiting-list') }}">{{ __('Review waiting list') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ url('/max-limits') }}">{{ __('Max request limits') }}</a>
                                    @if ($isSuperAdmin)
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ route('liveform-settings.edit') }}">{{ __('Open / close enrolment form') }}</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ route('app-version-settings.edit') }}">{{ __('App version settings') }}</a>
                                    @endif
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ url('/entry-questions') }}">{{ __('Sector questions control') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ url('/new-enrolments/analytics') }}">{{ __('Enrolment analytics') }}</a>

                                    @if ($isSuperAdmin)
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ url('/new-enrolments/migrations') }}">{{ __('Migrate requests to main system') }}</a>

                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ url('/person/change-qetaa') }}">{{ __('Change person sector') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif




                    {{-- ===================== Federations (visible to any logged-in user) ===================== --}}
                    @if ($isSuperAdmin || $isAdminSecretary || $isSecretary || $isAdminFinance || $isFinance)
                        <div class="px-3 mb-2">
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                    <span class="font-medium">{{ __('Registrations') }}</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('guests.index') }}">{{ __('Manage guests') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('family-members.index') }}">{{ __('Manage family') }}</a>
                                    @if ($isSuperAdmin)
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ route('person-tree.index') }}">{{ __('People tree') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif



                    {{-- ===================== Team Data ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">{{ __('Team data') }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('qetaa.tree') }}">{{ __('Team structure') }}</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('qetaa.auxiliary') }}">{{ __('View patrols') }}</a>
                            </div>
                        </div>
                    </div>



                    {{-- ===================== Media ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">{{ __('Media') }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                @if ($isSuperAdmin || $isMedia)
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('media.index') }}">{{ __('Add photos') }}</a>
                                @endif
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('media.pages') }}">{{ __('View photos') }}</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Games ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">{{ __('Games') }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">

                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('games.index') }}">{{ __('Manage games') }}</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Finance (only SuperAdmin/Finance) ===================== --}}
                    @if ($isSuperAdmin || $isFinance || $isAdminFinance)
                        <div class="px-3 mb-2">
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                    <span class="font-medium">{{ __('Finance') }}</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                    @if ($isSuperAdmin || $isFinance)
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ route('finance.index') }}">{{ __('Manage finance') }}</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ route('eventBookingFinance.selector') }}">{{ __('Manage booking finance') }}</a>
                                        <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                            href="{{ route('eventWaitingList.selector') }}">{{ __('Booking finance waiting list') }}</a>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ===================== Curricula (visible to any logged-in user) ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">{{ __('Curricula') }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('CurriculaCategory.index') }}">{{ __('Add categories') }}</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('curricula.index') }}">{{ __('Add lecture') }}</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Secretary ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">{{ __('Secretariat') }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                @if ($isSuperAdmin || $isSecretary || $isAdminSecretary)
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('secretary.index') }}">{{ __('Add meeting minutes') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('locations.index') }}">{{ __('Add location') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('place.index') }}">{{ __('Add place type') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('admin.place_bookings.index') }}">{{ __('Manage place booking requests') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('event.index') }}">{{ __('Scout events') }}</a>
                                @endif

                                {{-- Always visible to any logged-in user --}}
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('place_bookings.my') }}">{{ __('My place booking requests') }}</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== Inventory ===================== --}}

                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">{{ __('Inventory') }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                @if ($isSuperAdmin || $isInventory || $isAdminInventory)
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('inventory.index') }}">{{ __('Custody items') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('inventory-issue.index') }}">{{ __('Print custody') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('admin.custody_requests.index') }}">{{ __('Follow up custody') }}</a>
                                @endif
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('custody_requests.my') }}">{{ __('Request custody') }}</a>
                            </div>
                        </div>
                    </div>

                    {{-- ===================== First Aid ===================== --}}
                    @if ($isSuperAdmin || $isAdminFirstAid)
                        <div class="px-3 mb-2">
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                    <span class="font-medium">{{ __('First aid') }}</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">

                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('medicine.index') }}">{{ __('Medicine stock') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('medicine.dispense') }}">{{ __('Dispense medicine') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('medicine.records') }}">{{ __('Medicine dispense log') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('medicine.locks') }}">{{ __('Reserve medicine') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('medicine.locations') }}">{{ __('Medicine locations') }}</a>

                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ===================== Persons Data ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">{{ __('Members data') }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                @if ($isSuperAdmin)
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('person.ShowPersons') }}">{{ __('All members data') }}</a>
                                @endif
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('person.index', ['id' => Auth::user()->id]) }}">
                                    {{ __('Members data') }}
                                </a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('attendance.manage') }}">{{ __('Attendance') }}</a>
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('attendance.scan') }}">{{ __('Scan attendance') }}</a>
                                @if ($isSuperAdmin || $isAdminQetaa)
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('personspecialcase.index') }}">{{ __('Special cases') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('personblacklist.index') }}">{{ __('Blacklist') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('personexammark.index') }}">{{ __('Exam marks') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('eventServantFollowup.selector') }}">{{ __('Follow up member bookings') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ===================== SuperAdmin tools ===================== --}}
                    @if ($isSuperAdmin)
                        <div class="px-3 mb-2">
                            <div x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                    <span class="font-medium">{{ __('SuperAdmin') }}</span>
                                    <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('admin.passwords') }}">{{ __('View & edit passwords') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('app-version-settings.edit') }}">{{ __('App version settings') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('audit-logs.index') }}">{{ __('Audit log') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('whatsapp.status') }}">{{ __('WhatsApp') }}</a>
                                    <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                        href="{{ route('whatsapp.campaigns.index') }}">{{ __('WhatsApp campaigns') }}</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ===================== Profile ===================== --}}
                    <div class="px-3 mb-2">
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between p-3 text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                :class="{ 'bg-emerald-50 text-emerald-600': open }">
                                <span class="font-medium">{{ __('Profile') }}</span>
                                <svg class="w-4 h-4 transition-transform" :class="{ '-rotate-90': open }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition class="mt-2 pe-4 space-y-1">
                                <a class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    href="{{ route('profile.show') }}">{{ __('View profile') }}</a>
                            </div>
                        </div>
                    </div>

                </nav>




                <!-- Mobile Logout Footer -->
                <div class="p-4 border-t border-gray-200 dark:border-slate-800 lg:hidden shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-3 p-3 text-gray-700 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                            <span class="font-medium">{{ __('Log out') }}</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main id="main-content" class="flex-1 flex flex-col min-w-0 w-full bg-gray-50 dark:bg-slate-950" tabindex="-1">
                <!-- Header Bar -->
                <header class="bg-white/95 dark:bg-slate-900/90 shadow-sm border-b border-gray-200 dark:border-slate-800/80 px-4 py-3 sticky top-0 z-10 backdrop-blur-md"
                    style="display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;">

                    <!-- Start: Mobile menu button / Page title -->
                    <div class="flex items-center gap-3 justify-start">
                        <button id="sidebarToggle" type="button"
                            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg lg:hidden"
                            aria-label="{{ __('Menu') }}">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        @if (isset($pageTitle))
                            <h1 class="hidden lg:block text-lg font-bold text-gray-800 lg:text-xl truncate">
                                {{ $pageTitle }}</h1>
                        @endif
                    </div>

                    <!-- Center: Logo -->
                    <div class="flex items-center justify-center">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('img/shamandora.webp') }}" alt="Logo"
                                class="h-10 w-auto sm:h-10 lg:h-14 dark:hidden">
                            <img src="{{ asset('img/shamandora-dark.webp') }}" alt="Logo"
                                class="h-10 w-auto sm:h-10 lg:h-14 hidden dark:block">
                        </a>
                    </div>

                    <!-- End: help + theme + language + logout -->
                    <div class="flex items-center justify-end gap-1 sm:gap-2">
                        <x-page-help />

                        <button type="button" id="themeToggle"
                            class="inline-flex h-10 w-10 items-center justify-center text-gray-600 dark:text-emerald-300/90 hover:text-gray-900 dark:hover:text-emerald-200 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                            title="{{ __('Dark') }} / {{ __('Light') }}" aria-label="{{ __('Dark') }}">
                            <svg id="iconSun" class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 3v2.25M12 18.75V21m9-9h-2.25M5.25 12H3m15.364 6.364l-1.591-1.591M7.227 7.227 5.636 5.636m12.728 0-1.591 1.591M7.227 16.773l-1.591 1.591M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                            <svg id="iconMoon" class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                        </button>

                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open = !open"
                                class="inline-flex h-10 w-10 items-center justify-center text-xs sm:text-sm font-semibold text-gray-700 dark:text-emerald-300/90 hover:text-gray-900 dark:hover:text-emerald-200 hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                                aria-label="{{ __('Language') }}">
                                {{ $locale === 'ar' ? 'ع' : 'EN' }}
                            </button>
                            <div x-show="open" @click.outside="open = false" x-cloak
                                class="absolute {{ $isRtl ? 'left-0' : 'right-0' }} mt-1 w-36 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg shadow-lg overflow-hidden z-20">
                                <a href="{{ route('locale.switch', 'ar') }}"
                                    class="block px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 {{ $locale === 'ar' ? 'font-bold text-emerald-600' : '' }}">
                                    {{ __('Arabic') }}
                                </a>
                                <a href="{{ route('locale.switch', 'en') }}"
                                    class="block px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 {{ $locale === 'en' ? 'font-bold text-emerald-600' : '' }}">
                                    {{ __('English') }}
                                </a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="hidden lg:block">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-2 px-3 py-2 text-gray-700 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                <span class="font-medium hidden xl:inline">{{ __('Log out') }}</span>
                            </button>
                        </form>
                    </div>

                </header>

                <!-- Content Area -->
                <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-slate-950">
                    <div class="p-4 lg:p-6 page-enter">
                        @yield('content')
                    </div>
                </div>
            </main>

        </div>

        <!-- Footer -->
        <footer class="bg-white dark:bg-slate-900 shadow-sm border-t border-gray-200 dark:border-slate-800 px-4 py-3 text-center shrink-0">
            <p class="text-sm text-gray-600 dark:text-slate-400">{{ __('All rights reserved. Shamandora Scout.') }}</p>
        </footer>

    </div>

    <!-- Page loading overlay -->
    <div id="pageLoadingOverlay"
        class="fixed inset-0 z-50 bg-white/70 dark:bg-slate-950/80 backdrop-blur-sm"
        style="display: none; align-items: center; justify-content: center;"
        aria-hidden="true" role="status" aria-live="polite">
        <div class="flex flex-col items-center gap-4">

            {{-- Spinner + Logo --}}
            <div class="relative flex items-center justify-center loading-logo-pulse" style="width: 160px; height: 160px;">

                {{-- Spinning ring --}}
                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 160 160" aria-hidden="true">
                    <circle cx="80" cy="80" r="72" fill="none" class="stroke-gray-200 dark:stroke-slate-700"
                        stroke-width="4" />
                    <circle cx="80" cy="80" r="72" fill="none" stroke="#1D9E75" stroke-width="4"
                        stroke-linecap="round" stroke-dasharray="110 340"
                        style="transform-origin: 80px 80px; animation: shamandora-spin 1.1s linear infinite;" />
                </svg>

                {{-- Logo circle --}}
                <div class="relative z-10 rounded-full bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-700 shadow-sm dark:shadow-[0_0_32px_rgba(16,185,129,0.15)] flex items-center justify-center overflow-hidden"
                    style="width: 128px; height: 128px;">
                    <img src="{{ asset('img/shamandora.webp') }}" alt=""
                        class="dark:hidden" style="width: 108px; height: 108px; object-fit: contain;">
                    <img src="{{ asset('img/shamandora-dark.webp') }}" alt=""
                        class="hidden dark:block" style="width: 108px; height: 108px; object-fit: contain;">
                </div>
            </div>

            {{-- Label --}}
            <p class="text-sm text-gray-500 dark:text-slate-400 loading-label-pulse">{{ __('Loading...') }}</p>
        </div>
    </div>


    <!-- JavaScript -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const pageLoadingOverlay = document.getElementById('pageLoadingOverlay');
        const isRtl = (sidebar?.dataset.dir || 'rtl') === 'rtl';
        const closedClass = isRtl ? 'translate-x-full' : '-translate-x-full';

        let loadingTimer = null;
        let hidingTimer = null;
        const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const showLoading = () => {
            if (!pageLoadingOverlay) return;
            if (hidingTimer) {
                clearTimeout(hidingTimer);
                hidingTimer = null;
            }
            pageLoadingOverlay.classList.remove('loading-overlay-exit');
            pageLoadingOverlay.style.display = 'flex';
            pageLoadingOverlay.setAttribute('aria-hidden', 'false');
            if (!reduceMotion) {
                pageLoadingOverlay.classList.add('loading-overlay-enter');
            }
        };

        const hideLoading = () => {
            if (loadingTimer) {
                clearTimeout(loadingTimer);
                loadingTimer = null;
            }
            if (!pageLoadingOverlay || pageLoadingOverlay.style.display === 'none') return;

            const finishHide = () => {
                pageLoadingOverlay.style.display = 'none';
                pageLoadingOverlay.classList.remove('loading-overlay-enter', 'loading-overlay-exit');
                pageLoadingOverlay.setAttribute('aria-hidden', 'true');
                hidingTimer = null;
            };

            if (reduceMotion) {
                finishHide();
                return;
            }

            pageLoadingOverlay.classList.remove('loading-overlay-enter');
            pageLoadingOverlay.classList.add('loading-overlay-exit');
            hidingTimer = setTimeout(finishHide, 180);
        };

        const showLoadingDelayed = () => {
            if (loadingTimer) clearTimeout(loadingTimer);
            if (hidingTimer) {
                clearTimeout(hidingTimer);
                hidingTimer = null;
            }
            loadingTimer = setTimeout(() => {
                showLoading();
            }, 120);
        };

        function openSidebar() {
            sidebar.classList.remove(closedClass);
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebarFunc() {
            sidebar.classList.add(closedClass);
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        sidebarToggle?.addEventListener('click', openSidebar);
        closeSidebar?.addEventListener('click', closeSidebarFunc);
        overlay?.addEventListener('click', closeSidebarFunc);

        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const root = document.documentElement;
            const nextDark = !root.classList.contains('dark');
            root.classList.toggle('dark', nextDark);
            try {
                localStorage.setItem('theme', nextDark ? 'dark' : 'light');
            } catch (e) {}
        });

        document.addEventListener('DOMContentLoaded', hideLoading);
        window.addEventListener('load', hideLoading);
        window.addEventListener('pageshow', hideLoading);

        document.querySelectorAll('a[href]').forEach((link) => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
            if (link.target === '_blank' || link.hasAttribute('download')) return;

            const url = new URL(link.href, window.location.origin);
            if (url.origin !== window.location.origin) return;

            link.addEventListener('click', (event) => {
                if (
                    event.defaultPrevented ||
                    event.button !== 0 ||
                    event.metaKey ||
                    event.ctrlKey ||
                    event.shiftKey ||
                    event.altKey
                ) {
                    return;
                }

                if (url.href === window.location.href) return;
                showLoadingDelayed();
            });
        });

        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', () => {
                showLoadingDelayed();
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeSidebarFunc();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && window.innerWidth < 1024) {
                closeSidebarFunc();
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
