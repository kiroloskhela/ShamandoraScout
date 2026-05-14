<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>كشافة الشمندورة - قائمة الانتظار</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 flex items-center justify-center px-4">

    <div class="w-full max-w-3xl">

        <!-- Card -->
        <div class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 overflow-hidden">

            <!-- Header -->
            <div class="px-6 py-10 border-b border-slate-200 bg-slate-50 text-center">

                <!-- Logo -->
                <img src="{{ asset('img/shamandora.png') }}" alt="Shamandora Logo"
                    class="mx-auto h-24 w-24 object-contain mb-4" />

                <h1 class="text-2xl md:text-3xl font-bold text-slate-900">
                    قائمة الانتظار
                </h1>

                <p class="text-slate-500 mt-2">
                    تم تسجيل طلبكم بنجاح
                </p>
            </div>

            <!-- Content -->
            <div class="p-6 md:p-8 text-center">

                <div class="rounded-2xl bg-amber-50 border border-amber-200 p-6 text-amber-900">

                    <div class="text-5xl mb-4">
                        ⏳
                    </div>

                    <div class="font-bold text-xl mb-3">
                        تم وضعكم في قائمة الانتظار
                    </div>

                    <div class="text-sm md:text-base leading-loose">
                        نظراً لاكتمال العدد الحالي في القطاع،
                        تم إدراج طلبكم في قائمة الانتظار لحين توفر أماكن جديدة.
                    </div>

                    <div class="mt-4 text-sm md:text-base leading-loose">
                        سيتم التواصل معكم فور توفر مكان مناسب بإذن الله.
                    </div>

                </div>

                <div class="my-6 h-px bg-slate-200"></div>

                <!-- Footer -->
                <div class="text-slate-600 text-sm">
                    <div>
                        Copyright &copy; Shamandora Scout 2024
                    </div>

                    <div class="mt-2 font-bold text-indigo-600 text-lg">
                        مجموعة الشمندورة الكشفية
                    </div>
                </div>

                <!-- Optional Button -->
                {{-- 
                <div class="mt-6">
                    <a href="{{ url('/') }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-amber-600 px-8 py-3 font-bold text-white shadow hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        الرجوع للرئيسية
                    </a>
                </div>
                --}}

            </div>
        </div>
    </div>

</body>

</html>
