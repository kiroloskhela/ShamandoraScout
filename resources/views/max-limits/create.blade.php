@extends('layouts.app', ['pageTitle' => __('Enrolment form limits')])

@section('content')
    <div class="flex place-content-center">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg p-8 w-full max-w-2xl border-2 border-blue-300 dark:border-slate-700">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-bold text-gray-800 dark:text-slate-100">{{ __('Add new enrolment limit') }}</h2>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{ __('Control data') }}</p>
            </div>

            <form method="POST" action="{{ route('max-limits.insert') }}">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="qetaa_id" class="block mb-2 text-sm text-gray-700 dark:text-slate-300">{{ __('Scout sector') }}</label>
                        <select id="qetaa_id" name="qetaa_id" required
                            class="w-full h-12 px-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-slate-600 focus:border-blue-500 focus:outline-none">
                            <option value="" selected disabled>{{ __('Select scout sector') }}</option>
                            @foreach ($qetaat as $qetaa)
                                <option value="{{ $qetaa->QetaaID }}" @selected(old('qetaa_id') == $qetaa->QetaaID)>
                                    {{ $qetaa->QetaaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sana_marhala_id" class="block mb-2 text-sm text-gray-700 dark:text-slate-300">{{ __('Academic stage') }}</label>
                        <select id="sana_marhala_id" name="sana_marhala_id" required
                            class="w-full h-12 px-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-slate-600 focus:border-blue-500 focus:outline-none">
                            <option value="" selected disabled>{{ __('Select academic stage') }}</option>
                            @foreach ($seneen_marahel as $sana_marhala)
                                <option value="{{ $sana_marhala->SanaMarhalaID }}" @selected(old('sana_marhala_id') == $sana_marhala->SanaMarhalaID)>
                                    {{ $sana_marhala->SanaMarhalaName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="joindate" class="block mb-2 text-sm text-gray-700 dark:text-slate-300">{{ __('Year') }}</label>
                        <select id="joindate" name="joindate" required
                            class="w-full h-12 px-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-slate-600 focus:border-blue-500 focus:outline-none">
                            @foreach ($season as $seasonYear)
                                <option value="{{ $seasonYear->SeasonYear }}"
                                    @selected(old('joindate', date('Y')) == $seasonYear->SeasonYear)>
                                    {{ $seasonYear->SeasonYear }}
                                </option>
                            @endforeach
                            @if ($season->isEmpty())
                                <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="max_limit" class="block mb-2 text-sm text-gray-700 dark:text-slate-300">{{ __('Maximum enrolment requests') }}</label>
                        <input type="text" id="max_limit" name="max_limit" value="{{ old('max_limit') }}" required
                            placeholder="{{ __('Enter maximum enrolment limit for this sector') }}"
                            class="w-full h-12 px-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 text-slate-600 focus:border-blue-500 focus:outline-none">
                    </div>

                    <div class="flex justify-center">
                        <button type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 text-sm font-medium tracking-wide rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">
                            {{ __('Confirm entry') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
