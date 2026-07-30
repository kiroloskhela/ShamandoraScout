@extends('layouts.app', ['pageTitle' => __('Camp programs')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-slate-100">{{ __('Camp leader programs') }}</h1>
            <a href="{{ route('event-program.guide') }}"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                {{ __('Download guide template') }}
            </a>
        </div>

        @if (session('error'))
            <div class="mb-4 p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-emerald-50 text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto bg-white dark:bg-slate-800 rounded-xl shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-slate-700 text-gray-600 dark:text-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-start">{{ __('SeasonEventID') }}</th>
                        <th class="px-4 py-3 text-start">{{ __('Occasion name') }}</th>
                        <th class="px-4 py-3 text-start">{{ __('Occasion type') }}</th>
                        <th class="px-4 py-3 text-start">{{ __('Season') }}</th>
                        <th class="px-4 py-3 text-start">{{ __('Program status') }}</th>
                        <th class="px-4 py-3 text-start">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr class="border-t border-gray-100 dark:border-slate-700">
                            <td class="px-4 py-3">{{ $event->SeasonEventID }}</td>
                            <td class="px-4 py-3 font-medium">{{ $event->EventName }}</td>
                            <td class="px-4 py-3">{{ $event->EventTypeName }}</td>
                            <td class="px-4 py-3">{{ $event->SeasonName }} {{ $event->SeasonYear }}</td>
                            <td class="px-4 py-3">
                                @if ($event->program_id)
                                    <span class="px-2 py-1 rounded text-xs {{ $event->program_status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $event->program_status === 'published' ? __('Published') : __('Draft') }}
                                    </span>
                                @else
                                    <span class="text-gray-400">{{ __('None') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 space-x-2 rtl:space-x-reverse">
                                <a class="text-emerald-600 hover:underline" href="{{ route('event-program.open', $event->SeasonEventID) }}">
                                    {{ $event->program_id ? __('Open program') : __('Create program') }}
                                </a>
                                @if ($event->program_id && $event->program_source_url)
                                    <form method="post" action="{{ route('event-program.refresh', $event->program_id) }}" class="inline">
                                        @csrf
                                        <button class="text-indigo-600 hover:underline">{{ __('Refresh') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                {{ __('No camp-type season events found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
