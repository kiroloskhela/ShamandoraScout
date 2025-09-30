<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نسيت كلمة المرور</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');

    body {
        font-family: 'Cairo', sans-serif;
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
                    <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">استرجاع كلمة المرور</h2>

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
                            <label class="block text-gray-700 text-sm font-medium mb-2">رقم الهاتف</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="input-field w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 placeholder-gray-500 focus:outline-none focus:border-gray-500"
                                placeholder="أدخل رقم الهاتف" required>
                        </div>

                        <!-- DOB -->
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">تاريخ الميلاد</label>
                            <input type="date" name="dob" value="{{ old('dob') }}"
                                class="input-field w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-800 placeholder-gray-500 focus:outline-none focus:border-gray-500"
                                required>
                        </div>

                        <!-- Submit -->
                        <button type="submit"
                            class="login-btn w-full py-3 px-6 bg-gray-800 hover:bg-gray-900 text-white font-semibold rounded-lg focus:outline-none cursor-pointer">
                            إرسال كلمة مرور مؤقتة
                        </button>
                    </form>

                    <!-- Back to login -->
                    <div class="text-center mt-6">
                        <a href="{{ route('login') }}"
                            class="text-gray-600 hover:text-gray-800 text-sm hover:underline transition-all duration-300">
                            العودة لتسجيل الدخول
                        </a>
                    </div>
                </div>
            </div>

            <!-- Logo / Side -->
            <div class="flex flex-col items-center justify-center text-gray-800 order-1 lg:order-2">
                <div class="mb-8">
                    <div
                        class="w-40 h-40 bg-gray-100 rounded-full flex items-center justify-center shadow-md border border-gray-200">
                        <img src="{{ asset('img/shamandora.png') }}" alt="الشعار">
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