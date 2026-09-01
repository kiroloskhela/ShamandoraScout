<!DOCTYPE html>
@php($locale = app()->getLocale())
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.csrf-keepalive')
    <title>{{ __('Forgot password') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @include('partials.fonts')
    <style>
        body {
            font-family: {{ $locale === 'ar' ? "'Cairo'" : "'Source Sans 3'" }}, sans-serif;
        }

        .input-field {
            transition: all 0.3s ease;
        }

        .input-field:focus {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #6b7280;
        }

        .login-btn {
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
        }
    </style>
</head>

<body class="bg-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-8 items-center min-h-[80vh]">

            <!-- Form -->
            <div class="order-2 lg:order-1">
                <div class="bg-white rounded-lg p-8 lg:p-12 shadow-lg border border-gray-100">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">{{ __('Reset password') }}</h2>

                    {{-- Alerts --}}
                    @if (session('success'))
                        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-center">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-center">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('forgot-password.handle') }}" class="space-y-6">
                        @csrf

                        <!-- Phone -->
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">{{ __('Phone number') }}</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="input-field w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 placeholder-gray-500 focus:outline-none focus:border-gray-500"
                                placeholder="{{ __('Enter phone number') }}" required>
                        </div>

                        <!-- DOB -->
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">{{ __('Date of birth') }}</label>
                            <input type="date" name="dob" value="{{ old('dob') }}"
                                class="input-field w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 placeholder-gray-500 focus:outline-none focus:border-gray-500"
                                required>
                        </div>

                        <!-- Submit -->
                        <button type="submit"
                            class="login-btn w-full py-3 px-6 bg-gray-800 hover:bg-gray-900 text-white font-semibold rounded-lg focus:outline-none cursor-pointer">
                            {{ __('Send temporary password') }}
                        </button>
                    </form>

                    {{-- Triggered when duplicates are found --}}
                    @if (session('need_raqam_qawmy'))
                        <div id="rq-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ __('Confirm identity') }}</h3>
                                <p class="text-sm text-gray-600 mb-4">
                                    {{ session('info') ?? __('Please enter your national ID to complete verification.') }}
                                </p>

                                <form method="POST" action="{{ route('forgot-password.handle') }}" class="space-y-4">
                                    @csrf
                                    {{-- Keep the original inputs --}}
                                    <input type="hidden" name="phone" value="{{ old('phone') }}">
                                    <input type="hidden" name="dob" value="{{ old('dob') }}">

                                    <div>
                                        <label class="block text-gray-700 text-sm font-medium mb-2">{{ __('National ID') }}</label>
                                        <input type="text" name="raqam_qawmy" pattern="\d{14}" maxlength="14"
                                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500"
                                            placeholder="{{ __('Enter national ID (14 digits)') }}" required>
                                        <p class="mt-1 text-xs text-gray-500">{{ __('Enter 14 digits with no spaces.') }}</p>
                                    </div>

                                    <div class="flex gap-3">
                                        <button type="submit"
                                            class="flex-1 py-3 bg-gray-800 hover:bg-gray-900 text-white font-semibold rounded-lg">
                                            {{ __('Confirm') }}
                                        </button>
                                        <button type="button" onclick="closeRaqamModal()"
                                            class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg">{{ __('Cancel') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <script>
                        function closeRaqamModal() {
                            const el = document.getElementById('rq-modal');
                            if (el) el.remove(); // simply remove it; user stays on the same page
                        }
                    </script>


                    <!-- Back to login -->
                    <div class="text-center mt-6">
                        <a href="{{ route('login-auth') }}"
                            class="text-gray-600 hover:text-gray-800 text-sm hover:underline transition-all duration-300">
                            {{ __('Back to login') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Logo / Side -->
            <div class="flex flex-col items-center justify-center text-gray-800 order-1 lg:order-2">
                <div class="mb-8">
                    <div
                        class="w-40 h-40 bg-gray-100 rounded-full flex items-center justify-center shadow-md border border-gray-200">
                        <img src="{{ asset('img/shamandora.webp') }}" alt="{{ __('Logo') }}">
                    </div>
                </div>
                <h1 class="text-4xl lg:text-4xl font-bold mb-4 text-center text-gray-800">
                    {{ __('Shamandora Scout Group') }}
                </h1>
                <p class="text-lg lg:text-xl text-center text-gray-600 max-w-md">
                    {{ __('A beacon of leadership and guidance on the scout journey') }}
                </p>
            </div>

        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.input-field');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.style.transform = 'translateY(-1px)';
            });
            input.addEventListener('blur', () => {
                input.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>

</html>
