<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');

        body {
            font-family: 'Cairo', sans-serif;
        }

        .input-field {
            transition: all 0.25s ease;
        }

        .input-field:focus {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #374151;
            background-color: #fff;
        }

        .login-btn {
            transition: all 0.25s ease;
        }

        .login-btn:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            transform: translateY(-1px);
        }
    </style>
</head>

<body class="bg-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-2 gap-8 items-center min-h-[80vh]">

            <!-- Login Form -->
            <div class="order-2 lg:order-1">
                <div class="bg-white rounded-2xl p-8 lg:p-12 shadow-lg border border-gray-100">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">تسجيل الدخول</h2>

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            بيانات الدخول غير صحيحة.
                        </div>
                    @endif

                    <form id="loginForm" class="space-y-6" method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        <!-- Person ID -->
                        <div>
                            <label for="person_id" class="block text-gray-700 text-sm font-medium mb-2">
                                رقم الهوية
                            </label>

                            <input type="text" id="person_id" name="person_id" value="{{ old('person_id') }}"
                                maxlength="20" inputmode="numeric" pattern="[0-9]*" autocomplete="username"
                                spellcheck="false" autocapitalize="off"
                                class="input-field w-full px-4 py-3 bg-gray-50 border rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none @error('person_id') border-red-400 @else border-gray-300 @enderror"
                                placeholder="أدخل رقم الهوية" required>

                            @error('person_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="person_password" class="block text-gray-700 text-sm font-medium mb-2">
                                كلمة المرور
                            </label>

                            <div class="relative">

                                <input type="password" id="person_password" name="person_password"
                                    autocomplete="current-password"
                                    class="input-field w-full px-4 py-3 pl-12 bg-gray-50 border rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none @error('person_password') border-red-400 @else border-gray-300 @enderror"
                                    placeholder="أدخل كلمة المرور" required>

                                <!-- Eye Button -->
                                <button type="button" onclick="togglePassword()"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">

                                    <!-- Eye Open -->
                                    <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5
                    c4.478 0 8.268 2.943 9.542 7
                    -1.274 4.057-5.064 7-9.542 7
                    -4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>

                                    <!-- Eye Closed -->
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

                            @error('person_password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>



                        <!-- Forgot Password -->
                        <div class="text-right">
                            <a href="{{ url('forgot-password') }}"
                                class="text-gray-600 hover:text-gray-800 text-sm hover:underline transition-all duration-300">
                                نسيت كلمة المرور؟
                            </a>
                        </div>

                        <!-- Submit -->
                        <button type="submit" id="submit-button"
                            class="login-btn w-full py-3 px-6 bg-gray-800 hover:bg-gray-900 text-white font-semibold rounded-lg focus:outline-none disabled:opacity-70 disabled:cursor-not-allowed">
                            دخـــول
                        </button>
                    </form>

                    <!-- Sign Up Link -->
                    <div class="text-center mt-6">
                        <p class="text-gray-600">
                            ليس لديك حساب؟
                            <a href="#"
                                class="text-gray-800 hover:underline font-medium transition-all duration-300">
                                إنشاء حساب جديد
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Logo Section -->
            <div class="flex flex-col items-center justify-center text-gray-800 order-1 lg:order-2">
                <div class="mb-8">
                    <div
                        class="w-40 h-40 bg-gray-100 rounded-full flex items-center justify-center shadow-md border border-gray-200 overflow-hidden">
                        <img src="{{ asset('img/shamandora.png') }}" alt="شعار الشمندورة"
                            class="w-full h-full object-contain">
                    </div>
                </div>

                <h1 class="text-4xl lg:text-4xl font-bold mb-4 text-center text-gray-800">
                    مجموعة الشمندورة الكشافة
                </h1>

                <p class="text-lg lg:text-xl text-center text-gray-600 max-w-md">
                    منارة للقيادة والتوجيه في رحلة الكشافة
                </p>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitButton = document.getElementById('submit-button');
        const personIdInput = document.getElementById('person_id');

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
            form.addEventListener('submit', function() {
                if (personIdInput) {
                    personIdInput.value = personIdInput.value.replace(/\D+/g, '');
                }

                submitButton.disabled = true;
                submitButton.textContent = 'جارٍ تسجيل الدخول...';
            });
        }

        const inputs = document.querySelectorAll('.input-field');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.style.transform = 'translateY(-1px)';
            });

            input.addEventListener('blur', () => {
                input.style.transform = 'translateY(0)';
            });
        });

        function togglePassword() {

            const passwordInput = document.getElementById('person_password');

            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (passwordInput.type === 'password') {

                passwordInput.type = 'text';

                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');

            } else {

                passwordInput.type = 'password';

                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</body>

</html>
