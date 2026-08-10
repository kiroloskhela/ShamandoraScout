@extends('layouts.app', ['pageTitle' => __('My program')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">{{ __('My camp programs') }}</h1>

        @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($programs as $p)
                <a href="{{ route('my-program.show', $p->SeasonEventID) }}"
                    class="block bg-white dark:bg-slate-800 rounded-xl shadow p-5 hover:ring-2 hover:ring-emerald-500 transition">
                    <h2 class="text-lg font-semibold">{{ $p->title ?: $p->EventName }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $p->EventName }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ $p->EventStartDate }} – {{ $p->EventEndDate }}</p>
                </a>
            @empty
                <p class="text-gray-500">{{ __('No published programs assigned to you yet.') }}</p>
            @endforelse
        </div>
    </div>
@endsection
