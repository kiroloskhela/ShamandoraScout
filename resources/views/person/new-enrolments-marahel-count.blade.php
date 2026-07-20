@extends('layouts.app', ['pageTitle' => __('New enrolments counts by stage')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        @php
            $rows = collect($counts)
                ->map(function ($count, $stageId) {
                    return [
                        'SanaMarhalaID' => $stageId,
                        'Count' => $count,
                    ];
                })
                ->values()
                ->toArray();
        @endphp

        <x-data-table :data="$rows" :title="__('New enrolments counts by stage')" :columns="[
            [
                'key' => 'SanaMarhalaID',
                'label' => __('Stage ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'Count',
                'label' => __('Current count'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
        ]" :searchable="true" :sortable="true" :pagination="true" :per-page="20" />
    </div>
@endsection
