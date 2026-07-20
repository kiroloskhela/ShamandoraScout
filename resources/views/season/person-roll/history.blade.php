@extends('layouts.app', ['pageTitle' => __('Season roll history')])

@section('content')
<div class="container mx-auto px-4 py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-slate-50">{{ __('Season roll history') }}</h1>
            @if ($season)
                <p class="mt-1 text-sm text-gray-600 dark:text-slate-300">
                    {{ __('Active season') }}:
                    {{ __('Season: :name (:year)', ['name' => $season->SeasonName, 'year' => $season->SeasonYear]) }}
                </p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('season-person-roll.preview') }}"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                {{ __('Update persons for season') }}
            </a>
            <a href="{{ route('season.index') }}"
                class="inline-flex items-center rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                {{ __('Manage seasons') }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Batch ID') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Season') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Persons') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Sector changes') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Groups cleared') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Ran by') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Date') }}</th>
                    <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                @forelse ($batches as $batch)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $batch->id }}</td>
                        <td class="px-4 py-3 text-sm">{{ $batch->SeasonName }} ({{ $batch->SeasonYear }})</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                                {{ $batch->status === 'applied'
                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200'
                                    : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' }}">
                                {{ __($batch->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ $batch->persons_count }}</td>
                        <td class="px-4 py-3 text-sm">{{ $batch->qetaa_changed_count }}</td>
                        <td class="px-4 py-3 text-sm">{{ $batch->groups_cleared_count }}</td>
                        <td class="px-4 py-3 text-sm">{{ trim($batch->RanByName ?? '') ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm">{{ $batch->created_at }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($batch->status === 'applied')
                                <form method="POST" action="{{ route('season-person-roll.rollback', $batch->id) }}"
                                    onsubmit="return confirm(@js(__('Roll back this season person update? School years, sectors, and groups will be restored.')))">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                        {{ __('Rollback') }}
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-slate-500">{{ __('No season roll batches yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
