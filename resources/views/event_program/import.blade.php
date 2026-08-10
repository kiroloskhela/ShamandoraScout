@extends('layouts.app', ['pageTitle' => __('Import program')])

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <a href="{{ route('event-program.show', $program->id) }}" class="text-sm text-emerald-600 hover:underline">← {{ __('Back') }}</a>
        <h1 class="text-2xl font-bold mt-2 mb-4">{{ __('Import camp program') }}</h1>
        <p class="text-sm text-gray-500 mb-4">
            {{ __('Download the guide template, fill it, then upload the xlsx — or paste a public Google Sheets link.') }}
            {{ __('Google link is saved so you can Refresh later after small mission edits.') }}
        </p>

        @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-5 space-y-4">
            <a href="{{ route('event-program.guide') }}" class="inline-block px-4 py-2 rounded-lg border">{{ __('Download guide template') }}</a>

            <form method="post" action="{{ route('event-program.import.store', $program->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm mb-1">{{ __('Excel / CSV file') }}</label>
                    <input type="file" name="file" accept=".xlsx,.csv,.zip" class="w-full">
                </div>
                <div class="text-center text-gray-400 text-sm">{{ __('or') }}</div>
                <div>
                    <label class="block text-sm mb-1">{{ __('Google Sheets URL') }}</label>
                    <input type="url" name="google_url" value="{{ old('google_url') }}"
                        placeholder="https://docs.google.com/spreadsheets/d/..."
                        class="w-full border rounded-lg px-3 py-2 dark:bg-slate-900">
                    <p class="text-xs text-gray-500 mt-1">{{ __('Sheet must be viewable by anyone with the link.') }}</p>
                </div>
                <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">{{ __('Start import') }}</button>
            </form>
        </div>
    </div>
@endsection
