<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعيين كلمة سر جديدة</title>
    <link rel="icon" type="image/webp" href="{{ asset('img/shamandora.webp') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; }
        .no-copy {
            -webkit-user-select: none;
            user-select: none;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md mx-auto bg-white rounded-lg p-8 shadow-lg border border-gray-100">
        <div class="flex justify-center mb-4">
            <img src="{{ asset('img/shamandora.webp') }}" alt="شعار الشمندورة" class="h-20 w-20 object-contain">
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">تعيين كلمة سر جديدة</h2>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.reset.submit') }}" class="space-y-5" autocomplete="off">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">كلمة السر الجديدة</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required minlength="8"
                        autocomplete="new-password"
                        class="no-copy w-full px-4 py-3 pl-12 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500"
                        placeholder="8 أحرف على الأقل">
                    <button type="button" data-toggle-password="password"
                        class="absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 hover:text-gray-800"
                        aria-label="إظهار كلمة السر">
                        <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="eye-closed h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">تأكيد كلمة السر</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                        autocomplete="new-password"
                        class="no-copy w-full px-4 py-3 pl-12 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500"
                        placeholder="أعد إدخال كلمة السر">
                    <button type="button" data-toggle-password="password_confirmation"
                        class="absolute inset-y-0 left-0 flex items-center px-3 text-gray-500 hover:text-gray-800"
                        aria-label="إظهار كلمة السر">
                        <svg class="eye-open h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="eye-closed h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full py-3 bg-gray-800 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                حفظ كلمة السر
            </button>
        </form>
    </div>

    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-toggle-password'));
                var openIcon = btn.querySelector('.eye-open');
                var closedIcon = btn.querySelector('.eye-closed');
                var showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                openIcon.classList.toggle('hidden', !showing);
                closedIcon.classList.toggle('hidden', showing);
            });
        });

        document.querySelectorAll('.no-copy').forEach(function (el) {
            ['copy', 'cut', 'paste', 'contextmenu', 'selectstart', 'dragstart'].forEach(function (evt) {
                el.addEventListener(evt, function (e) { e.preventDefault(); return false; });
            });
            el.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && ['c', 'x', 'v', 'a'].includes(e.key.toLowerCase())) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>
