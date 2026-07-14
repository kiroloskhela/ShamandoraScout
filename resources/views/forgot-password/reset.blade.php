<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعيين كلمة سر جديدة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>

<body class="bg-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md mx-auto bg-white rounded-lg p-8 shadow-lg border border-gray-100">
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

        <form method="POST" action="{{ route('password.reset.submit') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">كلمة السر الجديدة</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500"
                    placeholder="8 أحرف على الأقل">
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-medium mb-2">تأكيد كلمة السر</label>
                <input type="password" name="password_confirmation" required minlength="8"
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500"
                    placeholder="أعد إدخال كلمة السر">
            </div>

            <button type="submit"
                class="w-full py-3 bg-gray-800 text-white rounded-lg font-semibold hover:bg-gray-700 transition">
                حفظ كلمة السر
            </button>
        </form>
    </div>
</body>

</html>
