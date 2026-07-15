<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>كشافة الشمندورة | الشخص موجود بالفعل</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-white text-slate-800">
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-3xl">

            <div class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 overflow-hidden">
                <div class="p-6 sm:p-10">

                    <div class="flex justify-center mb-6">
                        <div
                            class="h-28 w-28 rounded-full bg-white ring-4 ring-white shadow-md border border-slate-200 overflow-hidden">
                            <img src="{{ asset('img/shamandora.png') }}" class="h-full w-full object-contain p-3"
                                alt="Shamandora" />
                        </div>
                    </div>

                    <div class="text-center">
                        <div
                            class="inline-flex items-center rounded-full bg-rose-100 px-4 py-2 text-sm font-bold text-rose-700 mb-4">
                            الشخص موجود بالفعل
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                            لا يمكن استكمال التسجيل
                        </h1>

                        <p class="mt-4 text-sm sm:text-base leading-8 text-slate-600 max-w-2xl mx-auto">
                            يبدو أن هذا الشخص مسجل بالفعل على النظام.
                            من فضلك تأكد من البيانات التي قمت بإدخالها، خاصة الرقم القومي والاسم وتاريخ الميلاد.
                        </p>
                    </div>

                    <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center">
                        <p class="text-sm leading-7 text-slate-700">
                            إذا كنت متأكدًا أن البيانات صحيحة، فراجع التسجيل السابق أو تواصل مع المسؤول.
                        </p>
                    </div>


                    <div class="mt-8 flex flex-col sm:flex-row gap-3 sm:justify-center"> <button type="button"
                            onclick="history.back()"
                            class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-6 py-3 text-base font-bold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            الرجوع للصفحة السابقة </button> <a href="{{ route('person.liveform-create') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-6 py-3 text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">
                            العودة لبداية التسجيل </a> </div>

                    <div class="pt-6 mt-10 border-t border-slate-200 text-center">
                        <p class="text-xs text-slate-500">© 2024 Shamandora Scout</p>
                        <p class="text-sm font-bold text-indigo-700 mt-1">{{ __('Shamandora Scout Group') }}</p>
                    </div>

                </div>
            </div>

        </div>
    </main>
</body>

</html>
