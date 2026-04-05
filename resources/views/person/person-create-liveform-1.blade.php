<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>كشافة الشمندورة - التحاق جديد</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Cairo font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="{{ asset('img/shamandora.png') }}">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        select {
            height: 50px;
        }
    </style>
</head>

<body class="min-h-screen bg-white text-slate-800">
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-3xl">

            <!-- Card -->
            <div class="rounded-3xl bg-white shadow-xl ring-1 ring-slate-200 overflow-hidden">

                <div class="p-6 sm:p-10">

                    <!-- Logo -->
                    <div class="flex justify-center mb-6">
                        <div
                            class="h-28 w-28 rounded-full bg-white ring-4 ring-white shadow-md border border-slate-200 overflow-hidden">
                            <img src="{{ asset('img/shamandora.png') }}" class="h-full w-full object-contain p-3"
                                alt="Shamandora" />
                        </div>
                    </div>

                    <!-- Header -->
                    <div class="text-center">
                        <h1 class="text-2xl font-bold text-slate-900">
                            التحاق جديد
                        </h1>
                        <p class="mt-2 text-sm text-slate-500">
                            الجزء الأول: البيانات الدراسية
                        </p>
                    </div>

                    <!-- Form -->
                    <form id="regForm" method="POST" action="{{ route('person.liveform-insert') }}"
                        class="mt-8 space-y-6" novalidate>
                        @csrf

                        <!-- السنة والمرحلة -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                السنة والمرحلة الدراسية <span class="text-rose-700">*</span>
                            </label>
                            <select required id="sana_marhala_id" name="sana_marhala_id"
                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5
                                focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="" disabled selected>اختر السنة والمرحلة الدراسية</option>
                                @foreach ($seneen_marahel as $sana_marhala)
                                    <option value="{{ $sana_marhala->SanaMarhalaID }}">
                                        {{ $sana_marhala->SanaMarhalaName }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="error hidden mt-1 text-sm text-rose-700">
                                هذا الحقل مطلوب
                            </p>
                        </div>

                        <!-- النوع -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">
                                نوع الملتحق <span class="text-rose-700">*</span>
                            </label>
                            <select required id="gender" name="gender"
                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5
                                focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="" disabled selected>اختر النوع</option>
                                <option value="Male">ذكر</option>
                                <option value="Female">أنثى</option>
                            </select>
                            <p class="error hidden mt-1 text-sm text-rose-700">
                                هذا الحقل مطلوب
                            </p>
                        </div>



                        <!-- checkbox -->
                        <div id="leadersCheckbox"
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 hidden">
                            <label class="text-sm font-semibold text-slate-700">
                                تقديم لمدرسة إعداد قادة؟
                            </label>
                            <input type="checkbox" name="newLeadersSchool"
                                class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-200" />
                        </div>


                        <!-- Submit -->
                        <button id="submitBtn" type="submit"
                            class="w-full rounded-2xl bg-indigo-600 px-4 py-3 font-bold text-white
                            shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400
                            disabled:opacity-50 disabled:cursor-not-allowed">
                            اضغط للاستمرار →
                        </button>

                        <!-- Footer -->
                        <div class="pt-4 border-t border-slate-200 text-center">
                            <p class="text-xs text-slate-500">© 2024 Shamandora Scout</p>
                            <p class="text-sm font-bold text-indigo-700">
                                مجموعة الشمندورة الكشفية
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- ================= Live Validation ================= -->
    <script>
        const form = document.getElementById('regForm');
        const submitBtn = document.getElementById('submitBtn');
        const touched = new Set();

        function showError(el, show) {
            const wrapper = el.parentElement;
            const msg = wrapper.querySelector('.error');

            if (show) {
                el.classList.add('border-rose-600', 'ring-2', 'ring-rose-200');
                el.classList.remove('border-slate-300');
                if (msg) msg.classList.remove('hidden');
            } else {
                el.classList.remove('border-rose-600', 'ring-2', 'ring-rose-200');
                el.classList.add('border-slate-300');
                if (msg) msg.classList.add('hidden');
            }
        }

        function validateField(el) {
            if (!touched.has(el.id)) return true;
            const ok = el.value.trim() !== '';
            showError(el, !ok);
            return ok;
        }

        function validateAll() {
            let ok = true;
            document.querySelectorAll('.field[required]').forEach(el => {
                if (!validateField(el)) ok = false;
            });
            submitBtn.disabled = !ok;
            return ok;
        }

        form.addEventListener('blur', e => {
            if (!e.target.classList.contains('field')) return;
            touched.add(e.target.id);
            validateField(e.target);
            validateAll();
        }, true);

        form.addEventListener('change', e => {
            if (!e.target.classList.contains('field')) return;
            if (!touched.has(e.target.id)) return;
            validateField(e.target);
            validateAll();
        });

        form.addEventListener('submit', e => {
            document.querySelectorAll('.field[required]').forEach(el => touched.add(el.id));
            if (!validateAll()) {
                e.preventDefault();
                const firstError = document.querySelector('.ring-rose-200');
                if (firstError) {
                    firstError.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }
        });

        submitBtn.disabled = true;




        const sanaMarhalaSelect = document.getElementById('sana_marhala_id');
        const leadersCheckbox = document.getElementById('leadersCheckbox');

        sanaMarhalaSelect.addEventListener('change', function() {
            const selectedValue = parseInt(sanaMarhalaSelect.value);
            if (selectedValue >= 14) {
                leadersCheckbox.classList.remove('hidden');
            } else {
                leadersCheckbox.classList.add('hidden');
            }
        });
    </script>
</body>

</html>
