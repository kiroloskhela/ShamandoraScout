<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التسجيل مغلق | كشافة الشمندورة</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/shamandora.png') }}">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        .sea-bg {
            background:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(13, 148, 136, 0.25), transparent),
                linear-gradient(165deg, #f0fdfa 0%, #ecfeff 40%, #f8fafc 100%);
        }
    </style>
</head>
<body class="sea-bg min-h-screen flex flex-col">
    <main class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-lg text-center">
            <img src="{{ asset('img/shamandora.png') }}" alt="الشمندورة" class="mx-auto h-24 w-24 object-contain drop-shadow-md">
            <div class="mt-8 rounded-3xl bg-white/90 shadow-xl ring-1 ring-teal-100 px-8 py-10 backdrop-blur">
                <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-teal-50 text-teal-700 ring-1 ring-teal-100">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-teal-900">لا يوجد تسجيل حالياً</h1>
                <p class="mt-4 text-base leading-relaxed text-slate-600">{{ $message }}</p>
            </div>
            <p class="mt-8 text-xs text-slate-400">© {{ date('Y') }} مجموعة الشمندورة الكشفية البحرية — الإسكندرية</p>
        </div>
    </main>
</body>
</html>
