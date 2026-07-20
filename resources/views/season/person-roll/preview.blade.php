@extends('layouts.app', ['pageTitle' => __('Update persons for season')])

@section('content')
<div class="container mx-auto px-4 py-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-slate-50">{{ __('Update persons for season') }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-slate-300">
                @if ($season)
                    {{ __('Season: :name (:year)', ['name' => $season->SeasonName, 'year' => $season->SeasonYear]) }}
                @else
                    {{ __('No active season selected.') }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('season.index') }}"
                class="inline-flex items-center rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                {{ __('Manage seasons') }}
            </a>
            <a href="{{ route('season-person-roll.history') }}"
                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                {{ __('Season roll history') }}
            </a>
        </div>
    </div>

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    @if ($blockedReason)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100">
            {{ __($blockedReason) }}
            @if ($openBatch)
                <a href="{{ route('season-person-roll.history') }}" class="ms-2 underline font-semibold">{{ __('Rollback') }}</a>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="text-xs text-slate-500">{{ __('Persons') }}</div>
            <div class="text-2xl font-bold">{{ $summary['persons'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="text-xs text-slate-500">{{ __('Sector changes') }}</div>
            <div class="text-2xl font-bold">{{ $summary['qetaa_changed'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="text-xs text-slate-500">{{ __('Groups to clear') }}</div>
            <div class="text-2xl font-bold">{{ $summary['groups_cleared'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="text-xs text-slate-500">{{ __('To leader prep') }}</div>
            <div class="text-2xl font-bold">{{ $summary['to_eadad_qada'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <div class="text-xs text-slate-500">{{ __('Cross-sector jumps') }}</div>
            <div class="text-2xl font-bold">{{ $summary['qetaa_cross'] ?? 0 }}</div>
        </div>
    </div>

    <p class="text-sm text-gray-600 dark:text-slate-300">
        {{ __('Academic year advances for everyone until graduate. Sector remaps only for the youth ladder. People in leader prep, leaders, or rovers keep their sector. Groups under a changed sector are cleared.') }}
    </p>

    @php
        $tableRows = collect($rows)->map(function ($row) {
            return [
                'PersonID' => $row['person_id'],
                'PersonName' => $row['person_name'],
                'OldSana' => $row['old_sana_name'],
                'NewSana' => $row['new_sana_name'],
                'OldQetaa' => $row['old_qetaa_name'],
                'NewQetaa' => $row['new_qetaa_name'],
                'JumpType' => __($row['jump_type']),
                'ClearGroups' => ! empty($row['will_clear_groups']) ? __('Yes') : __('No'),
            ];
        })->values();
    @endphp

    <x-data-table :data="$tableRows" :title="__('Preview changes')" :columns="[
            ['key' => 'PersonID', 'label' => __('Person ID'), 'type' => 'text'],
            ['key' => 'PersonName', 'label' => __('Name'), 'type' => 'text'],
            ['key' => 'OldSana', 'label' => __('Current school year'), 'type' => 'text'],
            ['key' => 'NewSana', 'label' => __('New school year'), 'type' => 'text'],
            ['key' => 'OldQetaa', 'label' => __('Current sector'), 'type' => 'text'],
            ['key' => 'NewQetaa', 'label' => __('New sector'), 'type' => 'text'],
            ['key' => 'JumpType', 'label' => __('Jump type'), 'type' => 'badge', 'filter' => true],
            ['key' => 'ClearGroups', 'label' => __('Clear groups'), 'type' => 'badge', 'filter' => true],
        ]" :actions="[]" :searchable="true" :sortable="true" :pagination="true" :per-page="25" />

    @if (! $blockedReason && ($summary['persons'] ?? 0) > 0)
        <form method="POST" action="{{ route('season-person-roll.apply') }}"
            class="rounded-lg border border-indigo-200 bg-indigo-50 p-5 dark:border-indigo-800 dark:bg-indigo-950/30"
            onsubmit="return confirm(@js(__('Apply season person roll? This updates school years and sectors. You can roll back later.')))">
            @csrf
            <label class="flex items-start gap-3 text-sm text-indigo-950 dark:text-indigo-100">
                <input type="checkbox" name="confirm" value="1" required class="mt-1 rounded border-indigo-300">
                <span>{{ __('I reviewed the preview and want to apply academic + sector updates for the active season.') }}</span>
            </label>
            <button type="submit"
                class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                {{ __('Confirm and apply') }}
            </button>
        </form>
    @endif
</div>
@endsection
