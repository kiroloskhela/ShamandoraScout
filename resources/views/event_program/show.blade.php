@extends('layouts.app', ['pageTitle' => $program->title])

@section('content')
    <div class="container mx-auto px-4 py-8 space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ route('event-program.index') }}" class="text-sm text-emerald-600 hover:underline">← {{ __('Back') }}</a>
                <h1 class="text-2xl font-bold mt-1 text-gray-900 dark:text-slate-100">{{ $program->title }}</h1>
                <p class="text-sm text-gray-500">
                    {{ $meta->EventName ?? '' }} · {{ $meta->EventTypeName ?? '' }} · SeasonEvent #{{ $program->SeasonEventID }}
                    ·
                    <span class="{{ $program->status === 'published' ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $program->status === 'published' ? __('Published') : __('Draft') }}
                    </span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('event-program.guide') }}" class="px-3 py-2 rounded-lg border">{{ __('Download guide template') }}</a>
                <a href="{{ route('event-program.import', $program->id) }}" class="px-3 py-2 rounded-lg bg-blue-600 text-white">{{ __('Import') }}</a>
                <form method="post" action="{{ route('event-program.refresh', $program->id) }}"
                    onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').textContent='{{ __('Refreshing...') }}';">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 rounded-lg bg-indigo-600 text-white disabled:opacity-50"
                        @if (! $program->source_url) disabled @endif
                        title="{{ $program->source_url ? __('Refresh from sheet') : __('Save a Google Sheets URL first') }}">
                        {{ __('Refresh from sheet') }}
                    </button>
                </form>
                @if ($program->status === 'published')
                    <form method="post" action="{{ route('event-program.unpublish', $program->id) }}">@csrf
                        <button class="px-3 py-2 rounded-lg bg-amber-500 text-white">{{ __('Unpublish') }}</button>
                    </form>
                @else
                    <form method="post" action="{{ route('event-program.publish', $program->id) }}">@csrf
                        <button class="px-3 py-2 rounded-lg bg-emerald-600 text-white">{{ __('Publish') }}</button>
                    </form>
                @endif
                <form method="post" action="{{ route('event-program.whatsapp', $program->id) }}">@csrf
                    <button class="px-3 py-2 rounded-lg bg-green-700 text-white">{{ __('WhatsApp draft') }}</button>
                </form>
            </div>
        </div>

        @if ($program->source_url || $program->last_refreshed_at)
            <p class="text-xs text-gray-500">
                @if ($program->source_url)
                    {{ __('Sheet source') }}:
                    <a href="{{ $program->source_url }}" target="_blank" class="text-emerald-600 break-all">{{ $program->source_url }}</a>
                @endif
                @if ($program->last_refreshed_at)
                    · {{ __('Last refresh') }}: {{ $program->last_refreshed_at->format('Y-m-d H:i') }}
                @endif
            </p>
        @endif

        @if (session('error'))
            <div class="p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="p-3 rounded bg-emerald-50 text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-4">
            <h2 class="font-semibold mb-3">{{ __('Program settings') }}</h2>
            <form method="post" action="{{ route('event-program.meta', $program->id) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm mb-1">{{ __('Title') }}</label>
                    <input name="title" value="{{ old('title', $program->title) }}" class="w-full border rounded-lg px-3 py-2 dark:bg-slate-900" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">{{ __('Intro template') }}</label>
                    <textarea name="intro_template" rows="3" class="w-full border rounded-lg px-3 py-2 dark:bg-slate-900">{{ old('intro_template', $program->intro_template) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm mb-1">{{ __('Outro template') }}</label>
                    <textarea name="outro_template" rows="2" class="w-full border rounded-lg px-3 py-2 dark:bg-slate-900">{{ old('outro_template', $program->outro_template) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm mb-1">{{ __('Google Sheets URL (for refresh)') }}</label>
                    <input name="source_url" type="url" value="{{ old('source_url', $program->source_url) }}"
                        placeholder="https://docs.google.com/spreadsheets/d/..."
                        class="w-full border rounded-lg px-3 py-2 dark:bg-slate-900">
                    <p class="text-xs text-gray-500 mt-1">{{ __('Refresh re-reads this sheet and updates missions/games without full setup.') }}</p>
                </div>
                <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white">{{ __('Save') }}</button>
            </form>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold">{{ __('Days') }}</h2>
            </div>
            <form method="post" action="{{ route('event-program.days.store', $program->id) }}" class="flex flex-wrap gap-2 mb-4">
                @csrf
                <input type="number" name="day_number" min="1" placeholder="{{ __('Day number') }}" class="border rounded-lg px-3 py-2 w-32 dark:bg-slate-900" required>
                <input type="text" name="label" placeholder="{{ __('Label') }}" class="border rounded-lg px-3 py-2 dark:bg-slate-900">
                <button class="px-3 py-2 rounded-lg bg-blue-600 text-white">{{ __('Add day') }}</button>
            </form>

            @forelse ($program->days as $day)
                <div class="border rounded-lg p-3 mb-4 dark:border-slate-600">
                    <h3 class="font-medium mb-2">{{ $day->label ?: __('Day') . ' ' . $day->day_number }}</h3>

                    <form method="post" action="{{ route('event-program.slots.store', $day->id) }}" class="grid md:grid-cols-5 gap-2 mb-3">
                        @csrf
                        <input type="time" name="start_time" class="border rounded-lg px-2 py-2 dark:bg-slate-900" required>
                        <input type="time" name="end_time" class="border rounded-lg px-2 py-2 dark:bg-slate-900" required>
                        <input type="text" name="activity_label" placeholder="{{ __('Activity') }}" class="border rounded-lg px-2 py-2 dark:bg-slate-900" required>
                        <select name="slot_kind" class="border rounded-lg px-2 py-2 dark:bg-slate-900">
                            <option value="general">general</option>
                            <option value="games">games</option>
                            <option value="lecture">lecture</option>
                            <option value="duty">duty</option>
                            <option value="other">other</option>
                        </select>
                        <button class="px-3 py-2 rounded-lg bg-slate-700 text-white">{{ __('Add slot') }}</button>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="text-gray-500">
                                    <th class="text-start py-1">{{ __('Time') }}</th>
                                    <th class="text-start py-1">{{ __('Activity') }}</th>
                                    <th class="text-start py-1">{{ __('Kind') }}</th>
                                    <th class="text-start py-1">{{ __('Assignments') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($day->slots as $slot)
                                    <tr class="border-t dark:border-slate-700 align-top">
                                        <td class="py-2">{{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}</td>
                                        <td class="py-2">{{ $slot->activity_label }}</td>
                                        <td class="py-2">{{ $slot->slot_kind }}</td>
                                        <td class="py-2">
                                            <ul class="mb-2 space-y-1">
                                                @foreach ($slot->assignments as $a)
                                                    <li>
                                                        <span class="font-medium">{{ $personNames[$a->person_id] ?? ('#'.$a->person_id) }}</span>:
                                                        {{ $a->mission_text }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <form method="post" action="{{ route('event-program.assignments.store', $slot->id) }}" class="flex flex-wrap gap-1">
                                                @csrf
                                                <input type="number" name="person_id" placeholder="PersonID" class="border rounded px-2 py-1 w-28 dark:bg-slate-900" required>
                                                <input type="text" name="mission_text" placeholder="{{ __('Mission') }}" class="border rounded px-2 py-1 dark:bg-slate-900">
                                                <button class="px-2 py-1 rounded bg-emerald-600 text-white">{{ __('Save') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm">{{ __('No days yet. Import a guide sheet or add a day.') }}</p>
            @endforelse
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow p-4">
            <h2 class="font-semibold mb-3">{{ __('Games & lectures') }}</h2>
            <form method="post" action="{{ route('event-program.resources.store', $program->id) }}" class="grid md:grid-cols-5 gap-2 mb-4">
                @csrf
                <select name="kind" class="border rounded-lg px-2 py-2 dark:bg-slate-900">
                    <option value="game">game</option>
                    <option value="lecture">lecture</option>
                </select>
                <input name="title" placeholder="{{ __('Title') }}" class="border rounded-lg px-2 py-2 dark:bg-slate-900" required>
                <input name="url" placeholder="URL" class="border rounded-lg px-2 py-2 dark:bg-slate-900">
                <select name="day_id" class="border rounded-lg px-2 py-2 dark:bg-slate-900">
                    <option value="">{{ __('Any day') }}</option>
                    @foreach ($program->days as $day)
                        <option value="{{ $day->id }}">{{ $day->label ?: $day->day_number }}</option>
                    @endforeach
                </select>
                <button class="px-3 py-2 rounded-lg bg-blue-600 text-white">{{ __('Add') }}</button>
            </form>
            <ul class="space-y-2 text-sm">
                @foreach ($program->resources as $r)
                    <li class="flex items-center justify-between gap-2 border-b dark:border-slate-700 py-2">
                        <div>
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-xs">{{ $r->kind }}</span>
                            {{ $r->title }}
                            @if ($href = \App\Support\SafeHttpUrl::sanitize($r->url ?? null))
                                — <a href="{{ $href }}" target="_blank" class="text-emerald-600">link</a>
                            @endif
                        </div>
                        <form method="post" action="{{ route('event-program.resources.destroy', $r->id) }}">@csrf @method('DELETE')
                            <button class="text-red-600 text-xs">{{ __('Delete') }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
