<!DOCTYPE html>
@php($locale = app()->getLocale())
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('Shamandora Scout - New enrolment') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#0f766e',
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/webp" href="{{ asset('img/shamandora.webp') }}">

    <style>
        body {
            font-family: {{ $locale === 'ar' ? "'Tajawal'" : "'Source Sans 3'" }}, sans-serif;
        }

        .sea-bg {
            background:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(13, 148, 136, 0.25), transparent),
                linear-gradient(165deg, #f0fdfa 0%, #ecfeff 40%, #f8fafc 100%);
        }

        select {
            height: 50px;
        }
    </style>
</head>

<body class="sea-bg min-h-screen text-slate-800">
    <main class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-xl">
            <div class="rounded-3xl bg-white/90 shadow-xl ring-1 ring-teal-100 overflow-hidden backdrop-blur">
                <div class="p-6 sm:p-10">
                    <div class="flex justify-center mb-6">
                        <img src="{{ asset('img/shamandora.webp') }}" alt="{{ __('Shamandora') }}"
                            class="h-24 w-24 object-contain drop-shadow-md" />
                    </div>

                    <div class="text-center">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-brand-900">{{ __('New enrolment application') }}</h1>
                        <p class="mt-3 text-sm sm:text-base text-slate-600 leading-relaxed">
                            {{ __('Join the Sea Shamandora Scout Group — Alexandria') }}
                        </p>
                    </div>

                    <form id="regForm" method="POST" action="{{ route('person.liveform-insert') }}"
                        class="mt-8 space-y-5" novalidate>
                        @csrf

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">
                                {{ __('Academic year & stage') }} <span class="text-rose-600">*</span>
                            </label>
                            <select required id="sana_marhala_id" name="sana_marhala_id"
                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5
                                focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
                                <option value="" disabled selected>{{ __('Choose academic year & stage') }}</option>
                                @foreach ($seneen_marahel as $sana_marhala)
                                    <option value="{{ $sana_marhala->SanaMarhalaID }}">
                                        {{ $sana_marhala->SanaMarhalaName }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="error hidden mt-1 text-sm text-rose-600">{{ __('This field is required') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">{{ __('Applicant gender') }}<span class="text-rose-600">*</span>
                            </label>
                            <select required id="gender" name="gender"
                                class="field w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5
                                focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
                                <option value="" disabled selected>{{ __('Choose gender') }}</option>
                                <option value="Male">{{ __('Male') }}</option>
                                <option value="Female">{{ __('Female') }}</option>
                            </select>
                            <p class="error hidden mt-1 text-sm text-rose-600">{{ __('This field is required') }}</p>
                        </div>

                        <div id="leadersCheckbox"
                            class="flex items-center justify-between rounded-xl border border-teal-100 bg-teal-50/80 px-4 py-3 hidden">
                            <label class="text-sm font-bold text-slate-700">
                                {{ __('Applying for new leaders school?') }}
                            </label>
                            <input type="checkbox" name="newLeadersSchool"
                                class="h-5 w-5 rounded border-slate-300 text-brand-700 focus:ring-brand-600" />
                        </div>

                        <button id="submitBtn" type="submit"
                            class="w-full rounded-2xl bg-brand-700 px-4 py-3.5 font-bold text-white
                            shadow-md shadow-teal-900/10 hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-600
                            disabled:opacity-50 disabled:cursor-not-allowed transition">
                            {{ __('Click to continue') }} ←
                        </button>
                    </form>
                </div>
            </div>

            <p class="mt-8 text-center text-xs text-slate-500">
                © {{ date('Y') }} {{ __('Sea Shamandora Scout Group — Alexandria') }}
            </p>
        </div>
    </main>

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
