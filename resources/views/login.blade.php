<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    @include('partials.seo-head', [
        'seoTitle' => __('Shamandora Scout'),
        'seoDescription' => __('Egyptian Sea Scout group. Official Shamandora Scout portal for activities, events, registration, and news.'),
        'seoCanonical' => url('/'),
        'seoUrl' => url('/'),
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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');

        body {
            font-family: 'Cairo', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        html.dark body {
            background-image:
                radial-gradient(ellipse 90% 60% at 50% -20%, rgba(13, 148, 136, 0.22), transparent 55%),
                radial-gradient(ellipse 50% 40% at 100% 100%, rgba(30, 64, 175, 0.14), transparent 50%),
                radial-gradient(ellipse 40% 30% at 0% 80%, rgba(5, 150, 105, 0.1), transparent 45%);
            background-attachment: fixed;
        }

        .input-field {
            transition: all 0.25s ease;
        }

        .input-field:focus {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #374151;
        }

        .dark .input-field:focus {
            border-color: #2dd4bf;
            background-color: #020617;
            box-shadow: 0 0 0 3px rgba(45, 212, 191, 0.18);
        }

        .login-btn {
            transition: all 0.25s ease;
        }

        .login-btn:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            transform: translateY(-1px);
        }

        .dark .login-btn:hover {
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
        }

        .login-card {
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .dark .login-card {
            box-shadow:
                0 0 0 1px rgba(51, 65, 85, 0.85),
                0 24px 48px rgba(0, 0, 0, 0.45);
        }
    </style>
    @include('partials.motion-styles')
</head>

<body class="bg-white dark:bg-slate-950 min-h-screen flex items-center justify-center p-4 text-gray-900 dark:text-slate-100">
    <div class="fixed top-4 {{ $isRtl ? 'left-4' : 'right-4' }} z-20 flex items-center gap-1 sm:gap-2">
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
                    class="block px-3 py-2 text-sm text-gray-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800 {{ $locale === 'ar' ? 'font-bold text-emerald-600 dark:text-emerald-400' : '' }}">
                    {{ __('Arabic') }}
                </a>
                <a href="{{ route('locale.switch', 'en') }}"
                    class="block px-3 py-2 text-sm text-gray-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-slate-800 {{ $locale === 'en' ? 'font-bold text-emerald-600 dark:text-emerald-400' : '' }}">
                    {{ __('English') }}
                </a>
            </div>
        </div>
    </div>

    <div class="w-full max-w-6xl mx-auto page-enter">
        <div class="grid lg:grid-cols-2 gap-8 items-center min-h-[80vh]">

            <div class="order-2 lg:order-1">
                <div class="login-card bg-white dark:bg-slate-900/95 rounded-2xl p-8 lg:p-12 shadow-lg border border-gray-100 dark:border-slate-700/80">
                    <h2 class="text-3xl font-bold text-gray-800 dark:text-slate-50 mb-2 text-center">{{ __('Log in') }}</h2>
                    <p class="text-center text-sm text-gray-500 dark:text-slate-400 mb-8">{{ __('Shamandora Scout') }}</p>

                    @error('login')
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 dark:bg-red-950/40 dark:border-red-900 px-4 py-3 text-sm text-red-700 dark:text-red-200"
                            role="alert">
                            <p class="font-medium">{{ $message }}</p>
                            <p class="mt-1 text-red-600/90 dark:text-red-200/90">{{ __('Check your person ID and password, then try again.') }}</p>
                        </div>
                    @enderror

                    <form id="loginForm" class="space-y-6" method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        <div>
                            <label for="person_id" class="block text-gray-700 dark:text-slate-300 text-sm font-medium mb-2">
                                {{ __('Person ID') }}
                            </label>

                            <input type="text" id="person_id" name="person_id" value="{{ old('person_id') }}"
                                maxlength="20" inputmode="numeric" pattern="[0-9]*" autocomplete="username"
                                spellcheck="false" autocapitalize="off" aria-describedby="person_id_error"
                                class="input-field w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border rounded-lg text-gray-800 dark:text-slate-100 placeholder-gray-400 focus:outline-none @error('person_id') border-red-400 @else border-gray-300 dark:border-slate-600 @enderror"
                                placeholder="{{ __('Enter person ID') }}" required>

                            @error('person_id')
                                <p id="person_id_error" class="mt-2 text-sm text-red-600 dark:text-red-300" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="person_password" class="block text-gray-700 dark:text-slate-300 text-sm font-medium mb-2">
                                {{ __('Password') }}
                            </label>

                            <div class="relative">
                                <input type="password" id="person_password" name="person_password"
                                    autocomplete="current-password" aria-describedby="capsLockWarning person_password_error"
                                    class="input-field w-full px-4 py-3 {{ $isRtl ? 'pl-12' : 'pr-12' }} bg-gray-50 dark:bg-slate-800 border rounded-lg text-gray-800 dark:text-slate-100 placeholder-gray-400 focus:outline-none @error('person_password') border-red-400 @else border-gray-300 dark:border-slate-600 @enderror"
                                    placeholder="{{ __('Enter password') }}" required>

                                <button type="button" onclick="togglePassword()"
                                    class="absolute {{ $isRtl ? 'left-3' : 'right-3' }} top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-slate-400"
                                    aria-label="{{ __('Password') }}">
                                    <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5
                    c4.478 0 8.268 2.943 9.542 7
                    -1.274 4.057-5.064 7-9.542 7
                    -4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19
                    c-4.478 0-8.268-2.943-9.542-7
                    a9.956 9.956 0 012.223-3.592M6.228 6.228
                    A9.956 9.956 0 0112 5c4.478 0 8.268 2.943
                    9.542 7a9.97 9.97 0 01-4.132 5.411M15 12
                    a3 3 0 11-6 0 3 3 0 016 0zm6 6L3 3" />
                                    </svg>
                                </button>
                            </div>

                            <p id="capsLockWarning" class="mt-2 text-sm text-amber-700 dark:text-amber-300 hidden" role="status" aria-live="polite">
                                {{ __('Caps Lock is on') }}
                            </p>

                            @error('person_password')
                                <p id="person_password_error" class="mt-2 text-sm text-red-600 dark:text-red-300" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="{{ $isRtl ? 'text-right' : 'text-left' }}">
                            <a href="{{ url('forgot-password') }}"
                                class="text-gray-600 dark:text-slate-400 hover:text-gray-800 text-sm hover:underline transition-all duration-300">
                                {{ __('Forgot password?') }}
                            </a>
                        </div>

                        <button type="submit" id="submit-button"
                            class="login-btn w-full py-3 px-6 bg-gray-800 hover:bg-gray-900 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white font-semibold rounded-lg focus:outline-none disabled:opacity-70 disabled:cursor-not-allowed">
                            {{ __('Log in') }}
                        </button>
                    </form>

                    <div class="text-center mt-6">
                        <p class="text-gray-600 dark:text-slate-400">
                            {{ __("Don't have an account?") }}
                            <a href="#"
                                class="text-gray-800 dark:text-slate-100 hover:underline font-medium transition-all duration-300">
                                {{ __('Create a new account') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center justify-center text-gray-800 dark:text-slate-100 order-1 lg:order-2">
                <div class="mb-8">
                    <div
                        class="w-40 h-40 bg-gray-100 dark:bg-slate-900 rounded-full flex items-center justify-center shadow-md dark:shadow-[0_0_40px_rgba(16,185,129,0.2)] border border-gray-200 dark:border-emerald-500/30 overflow-hidden ring-4 ring-transparent dark:ring-emerald-500/10">
                        <img src="{{ asset('img/shamandora.webp') }}" alt="{{ __('Shamandora Scout') }}"
                            class="h-24 w-24 object-contain dark:hidden">
                        <img src="{{ asset('img/shamandora-dark.webp') }}" alt="{{ __('Shamandora Scout') }}"
                            class="h-24 w-24 object-contain hidden dark:block">
                    </div>
                </div>

                <h1 class="text-4xl lg:text-4xl font-bold mb-4 text-center dark:text-transparent dark:bg-clip-text dark:bg-gradient-to-l dark:from-emerald-300 dark:to-teal-200">
                    {{ __('Shamandora Scout Group') }}
                </h1>

                <p class="text-lg lg:text-xl text-center text-gray-600 dark:text-slate-400 max-w-md">
                    {{ __('A beacon of leadership and guidance on the scout journey') }}
                </p>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitButton = document.getElementById('submit-button');
        const personIdInput = document.getElementById('person_id');
        const passwordInput = document.getElementById('person_password');
        const capsLockWarning = document.getElementById('capsLockWarning');
        const loggingInText = @json(__('Logging in...'));
        const pleaseEnterPersonId = @json(__('Please enter your person ID.'));
        const pleaseEnterPassword = @json(__('Please enter your password.'));

        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const root = document.documentElement;
            const nextDark = !root.classList.contains('dark');
            root.classList.toggle('dark', nextDark);
            try { localStorage.setItem('theme', nextDark ? 'dark' : 'light'); } catch (e) {}
        });

        function setCapsLockWarning(event) {
            if (!capsLockWarning || !event.getModifierState) return;
            const on = event.getModifierState('CapsLock');
            capsLockWarning.classList.toggle('hidden', !on);
        }

        if (passwordInput) {
            ['keydown', 'keyup', 'click', 'focus'].forEach((type) => {
                passwordInput.addEventListener(type, setCapsLockWarning);
            });
            passwordInput.addEventListener('blur', () => {
                capsLockWarning?.classList.add('hidden');
            });
        }

        if (personIdInput) {
            personIdInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D+/g, '');
            });

            personIdInput.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                this.value = pastedText.replace(/\D+/g, '');
            });
        }

        if (form && submitButton) {
            form.addEventListener('submit', function(e) {
                if (personIdInput) {
                    personIdInput.value = personIdInput.value.replace(/\D+/g, '');
                }

                const personId = personIdInput ? personIdInput.value.trim() : '';
                const password = passwordInput ? passwordInput.value : '';

                if (!personId || !password) {
                    e.preventDefault();
                    if (!personId && personIdInput) {
                        personIdInput.setCustomValidity(pleaseEnterPersonId);
                        personIdInput.reportValidity();
                        personIdInput.setCustomValidity('');
                    } else if (passwordInput) {
                        passwordInput.setCustomValidity(pleaseEnterPassword);
                        passwordInput.reportValidity();
                        passwordInput.setCustomValidity('');
                    }
                    return;
                }

                submitButton.disabled = true;
                submitButton.textContent = loggingInText;
            });
        }

        function togglePassword() {
            const input = document.getElementById('person_password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</body>

</html>
