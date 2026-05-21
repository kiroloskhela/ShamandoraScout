<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>كشافة الشمندورة | غير مسموح بالدخول</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-100 text-slate-800">

    <main class="min-h-screen flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-3xl">

            <div class="overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200">

                <!-- Header -->
                <div class="relative border-b border-slate-200 bg-slate-50 px-6 py-10 sm:px-10">

                    <div class="flex flex-col items-center text-center">

                        <!-- Logo -->
                        <div
                            class="mb-5 h-28 w-28 overflow-hidden rounded-full border border-slate-200 bg-white p-3 shadow-md ring-4 ring-white">
                            <img src="{{ asset('img/shamandora.png') }}" class="h-full w-full object-contain"
                                alt="Shamandora Logo">
                        </div>

                        <!-- Badge -->
                        <div
                            class="mb-5 inline-flex items-center gap-2 rounded-full bg-rose-100 px-4 py-2 text-sm font-bold text-rose-800 ring-1 ring-rose-200">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                            خطأ 403 - وصول مرفوض
                        </div>

                        <!-- Title -->
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                            غير مسموح لك بالدخول
                        </h1>

                        <!-- Description -->
                        <p class="mt-4 max-w-2xl text-sm sm:text-base leading-7 text-slate-600">
                            عذرًا، لا تملك الصلاحيات الكافية للوصول إلى هذه الصفحة.
                            إذا كنت تعتقد أن هذا خطأ، يمكنك التواصل مع المسؤولين أو تسجيل الدخول بحساب يمتلك الصلاحيات
                            المناسبة.
                        </p>

                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 sm:p-10">

                    <!-- Info Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">

                        <!-- Reason -->
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">

                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">

                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                                </svg>
                            </div>

                            <h3 class="font-bold text-slate-900">
                                سبب المشكلة
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                حسابك الحالي لا يمتلك صلاحية الوصول إلى هذه الصفحة أو هذا القسم من النظام.
                            </p>
                        </div>

                        <!-- Solution -->
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">

                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">

                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>

                            <h3 class="font-bold text-slate-900">
                                ماذا يمكنك أن تفعل؟
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                يمكنك العودة إلى الصفحة الرئيسية أو تسجيل الدخول بحساب آخر يمتلك الصلاحيات المطلوبة.
                            </p>
                        </div>

                    </div>

                    <!-- Important Note -->
                    <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">

                        <div class="flex items-start gap-3">

                            <div class="mt-0.5 text-amber-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12A9 9 0 1112 3a9 9 0 019 9z" />
                                </svg>
                            </div>

                            <div>
                                <h4 class="font-bold text-amber-900">
                                    ملاحظة مهمة
                                </h4>

                                <p class="mt-1 text-sm leading-6 text-amber-800">
                                    ظهور هذه الصفحة يعني أن النظام تحقق من صلاحيات الحساب الحالي ورفض عملية الوصول
                                    لحماية البيانات والمحتوى الداخلي.
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">

                        <!-- Home Button -->
                        <a href="{{ route('home') }}"
                            class="inline-flex w-full sm:w-auto items-center justify-center rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-indigo-700">

                            العودة للقائمة الرئيسية
                        </a>

                        <!-- Logout Button -->
                        @if (auth()->check())
                            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                                @csrf

                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 px-6 py-3 text-sm font-bold text-rose-700 transition hover:bg-rose-100">

                                    تسجيل الخروج
                                </button>
                            </form>
                        @endif

                    </div>

                    <!-- Footer -->
                    <div class="mt-10 border-t border-slate-200 pt-6 text-center">

                        <p class="text-xs text-slate-500">
                            © Shamandora Scout
                        </p>

                        <p class="mt-1 text-sm font-bold text-indigo-700">
                            مجموعة الشمندورة الكشفية
                        </p>

                    </div>

                </div>
            </div>
        </div>
    </main>

</body>

</html>
