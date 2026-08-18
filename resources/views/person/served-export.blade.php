@extends('layouts.app', ['pageTitle' => __('Download served people data')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-slate-100 mb-2">{{ __('Download served people data') }}</h1>
            <p class="text-gray-600 dark:text-slate-300">{{ __('Select the sector and season, then download Excel.') }}</p>
        </div>

        <form method="POST" action="{{ route('export.served-people.download') }}"
            class="bg-white dark:bg-slate-900 rounded-lg shadow-lg dark:border dark:border-slate-700 p-6 max-w-3xl mx-auto border-2 border-blue-300 dark:border-slate-700">
            @csrf
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="qetaa_id" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Choose sector') }}</label>
                    <select id="qetaa_id" name="qetaa_id" required
                        class="w-full h-12 ps-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-600 dark:text-slate-300 focus:border-blue-500 focus:outline-none">
                        <option value="">{{ __('-- Choose sector --') }}</option>
                        @foreach ($qetaas as $qetaa)
                            <option value="{{ $qetaa->QetaaID }}" @selected((string) old('qetaa_id') === (string) $qetaa->QetaaID)>
                                {{ $qetaa->QetaaName }}
                            </option>
                        @endforeach
                    </select>
                    @if ($qetaas->isEmpty())
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">{{ __('No sectors you can export.') }}</p>
                    @endif
                    @error('qetaa_id')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="season_id" class="block mb-2 text-sm font-semibold text-gray-700 dark:text-slate-200">{{ __('Choose season') }}</label>
                    <select id="season_id" name="season_id" required
                        class="w-full h-12 ps-4 border rounded-lg border-slate-200 dark:border-slate-700 dark:bg-slate-900 text-slate-600 dark:text-slate-300 focus:border-blue-500 focus:outline-none">
                        <option value="">{{ __('-- Choose season --') }}</option>
                        @foreach ($seasons as $season)
                            <option value="{{ $season->SeasonID }}" @selected((string) old('season_id') === (string) $season->SeasonID)>
                                {{ $season->SeasonName }} ({{ $season->SeasonYear }})
                            </option>
                        @endforeach
                    </select>
                    @error('season_id')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="mt-8 flex justify-center">
                <button type="submit"
                    class="h-12 px-6 rounded-lg bg-green-600 hover:bg-green-700 text-white font-bold transition-colors duration-200"
                    @disabled($qetaas->isEmpty())>
                    {{ __('Download Excel') }}
                </button>
            </div>
        </form>
    </div>
@endsection
