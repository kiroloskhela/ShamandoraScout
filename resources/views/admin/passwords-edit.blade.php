@extends('layouts.app', ['pageTitle' => __('Edit password')])

@section('content')
    @php
        $fullName = trim(implode(' ', array_filter([
            $user->FirstName ?? '',
            $user->SecondName ?? '',
            $user->ThirdName ?? '',
            $user->FourthName ?? '',
        ])));
    @endphp
    <div class="flex place-content-center">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg p-8 w-full max-w-md border-2 border-emerald-300 dark:border-slate-700">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800 dark:text-slate-100">
                    {{ __('Edit password for user: :name', ['name' => $fullName !== '' ? $fullName : __('User')]) }}
                </h2>
            </div>

            @if (session('success'))
                <div role="status"
                    class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/40 px-4 py-3 text-sm font-semibold text-emerald-800 dark:text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert"
                    class="mb-4 rounded-lg border border-rose-200 bg-rose-50 dark:bg-rose-900/40 px-4 py-3 text-sm text-rose-800 dark:text-rose-200">
                    <div class="font-bold mb-1">{{ __('Password does not meet the required rules.') }}</div>
                    <ul class="list-disc ps-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <dl class="mb-6 grid grid-cols-1 gap-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/60">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('User ID') }}</dt>
                    <dd class="font-semibold text-slate-800 dark:text-slate-100" dir="ltr">{{ $user->PersonID }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Full name') }}</dt>
                    <dd class="font-semibold text-slate-800 dark:text-slate-100 text-end">{{ $fullName !== '' ? $fullName : '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Shamandora code') }}</dt>
                    <dd class="font-semibold text-slate-800 dark:text-slate-100" dir="ltr">{{ $user->ShamandoraCode ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('National ID') }}</dt>
                    <dd class="font-semibold text-slate-800 dark:text-slate-100" dir="ltr">{{ $user->RaqamQawmy ?: '—' }}</dd>
                </div>
            </dl>

            <form id="admin-password-form" method="POST"
                action="{{ route('admin.passwords.update', $user->PersonID) }}">
                @csrf

                <div class="space-y-6">
                    <div class="relative">
                        <label for="password"
                            class="block mb-2 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('New password') }}</label>
                        <input id="password" type="password" name="password" required minlength="8" maxlength="72"
                            autocomplete="new-password" placeholder="{{ __('Enter new password') }}"
                            class="w-full h-12 px-4 text-sm border rounded-lg outline-none border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <ul id="password-rules" class="text-sm space-y-1.5 text-slate-500 dark:text-slate-400">
                        <li data-check="length">{{ __('At least 8 characters') }}</li>
                        <li data-check="upper">{{ __('At least one uppercase letter') }}</li>
                        <li data-check="lower">{{ __('At least one lowercase letter') }}</li>
                        <li data-check="number">{{ __('At least one number') }}</li>
                    </ul>

                    <p id="password-client-error" class="hidden text-sm font-semibold text-rose-600" role="alert">
                        {{ __('Password does not meet the required rules.') }}
                    </p>

                    <div class="flex justify-center gap-3">
                        <a href="{{ route('admin.passwords') }}"
                            class="inline-flex items-center justify-center h-12 px-6 text-sm font-medium rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50">{{ __('Back') }}</a>
                        <button type="submit" id="submit-button"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-emerald-50 text-emerald-600 hover:bg-emerald-100">
                            {{ __('Edit password') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const input = document.getElementById('password');
            const form = document.getElementById('admin-password-form');
            const items = document.querySelectorAll('#password-rules [data-check]');
            const clientError = document.getElementById('password-client-error');
            if (!input || !form) return;

            const passClass = 'text-emerald-600 dark:text-emerald-400 font-semibold';
            const failClass = 'text-slate-500 dark:text-slate-400';

            function checks(value) {
                return {
                    length: value.length >= 8,
                    upper: /[A-Z]/.test(value),
                    lower: /[a-z]/.test(value),
                    number: /\d/.test(value),
                };
            }

            function allPass(result) {
                return result.length && result.upper && result.lower && result.number;
            }

            function paint() {
                const result = checks(input.value);
                items.forEach(function(item) {
                    const ok = result[item.getAttribute('data-check')];
                    item.className = ok ? passClass : failClass;
                });
                return result;
            }

            input.addEventListener('input', function() {
                paint();
                if (clientError) clientError.classList.add('hidden');
            });

            form.addEventListener('submit', function(e) {
                const result = paint();
                if (allPass(result)) return;
                e.preventDefault();
                if (clientError) clientError.classList.remove('hidden');
            });

            paint();
        })();
    </script>
@endsection
