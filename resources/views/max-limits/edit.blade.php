@extends('layouts.app', ['pageTitle' => __('Enrolment form limits')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-emerald-300 dark:border-slate-700">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800 dark:text-slate-100">{{ __('Edit enrolment limit') }}</h2>
            </div>

            <form method="POST"
                action="{{ route('max-limits.update', ['id' => $marhalaSelected->QetaaID, 'sana_id' => $marhalaSelected->SanaMarhalaID]) }}">
                @csrf
                @method('PATCH')

                <div class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm text-gray-700 dark:text-slate-300">{{ __('Scout sector') }}</label>
                        <input type="text" value="{{ $marhalaSelected->QetaaName }}" disabled
                            class="w-full h-12 px-4 border rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 text-slate-600">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm text-gray-700 dark:text-slate-300">{{ __('Academic stage') }}</label>
                        <input type="text" value="{{ $marhalaSelected->SanaMarhalaName }}" disabled
                            class="w-full h-12 px-4 border rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 text-slate-600">
                    </div>

                    <div>
                        <label for="max_limit" class="block mb-2 text-sm text-gray-700 dark:text-slate-300">{{ __('Enrolment limit value') }}</label>
                        <input type="text" id="max_limit" name="max_limit" value="{{ old('max_limit', $marhalaSelected->MaxLimit) }}" required
                            class="w-full h-12 px-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-slate-600 focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition">
                            {{ __('Edit') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
