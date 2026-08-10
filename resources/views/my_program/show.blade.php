@extends('layouts.app', ['pageTitle' => $program->title])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <a href="{{ route('my-program.index') }}" class="text-sm text-emerald-600 hover:underline">← {{ __('Back') }}</a>
        <h1 class="text-2xl font-bold mt-2">{{ $program->title }}</h1>
        <p class="text-sm text-gray-500 mb-6">{{ $meta->EventName ?? '' }}</p>

        <div class="flex flex-wrap gap-2 mb-6">
            @foreach ($days as $day)
                <a href="{{ route('my-program.day', [$seasonEventId, $day['day_number']]) }}"
                    class="px-4 py-2 rounded-full bg-emerald-50 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200 hover:bg-emerald-100">
                    {{ $day['label'] }}
                </a>
            @endforeach
        </div>

        <p class="text-gray-600 dark:text-slate-300">{{ __('Open a day to see your missions, games, and lectures.') }}</p>
    </div>
@endsection
