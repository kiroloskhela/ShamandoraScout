@extends('layouts.app', ['pageTitle' => $day['label']])

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <a href="{{ route('my-program.show', $seasonEventId) }}" class="text-sm text-emerald-600 hover:underline">← {{ __('Back') }}</a>
        <h1 class="text-2xl font-bold mt-2">{{ $program->title }}</h1>
        <h2 class="text-lg text-gray-600 dark:text-slate-300 mb-6">{{ $day['label'] }}</h2>

        <div class="flex flex-wrap gap-2 mb-6">
            @foreach ($days as $d)
                <a href="{{ route('my-program.day', [$seasonEventId, $d['day_number']]) }}"
                    class="px-3 py-1 rounded-full text-sm {{ $d['day_number'] === $day['day_number'] ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700' }}">
                    {{ $d['label'] }}
                </a>
            @endforeach
        </div>

        <ol class="space-y-4">
            @foreach ($day['slots'] as $slot)
                <li class="bg-white dark:bg-slate-800 rounded-xl shadow p-4">
                    <div class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">
                        {{ $slot['start_time'] }} – {{ $slot['end_time'] }}
                    </div>
                    <div class="font-semibold mt-1">{{ $slot['mission_text'] ?: $slot['activity_label'] }}</div>
                    @if (!empty($slot['resources']))
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ($slot['resources'] as $r)
                                <li class="border-t dark:border-slate-700 pt-2">
                                    <span class="uppercase text-xs text-gray-400">{{ $r['kind'] }}</span>
                                    <div>{{ $r['title'] }}</div>
                                    @if (!empty($r['url']))
                                        <a href="{{ $r['url'] }}" target="_blank" rel="noopener" class="text-emerald-600 break-all">{{ $r['url'] }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
@endsection
