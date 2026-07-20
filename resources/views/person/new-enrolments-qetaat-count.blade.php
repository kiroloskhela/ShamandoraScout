@extends('layouts.app', ['pageTitle' => __('New enrolments counts by sector')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        @php
            $rows = collect($counts)
                ->map(function ($count, $sectorId) use ($qetaat) {
                    $sectorName = data_get($qetaat, $sectorId . '.0.QetaaName', __('Sector ID') . ' ' . $sectorId);

                    return [
                        'QetaaID' => $sectorId,
                        'QetaaName' => $sectorName,
                        'Count' => $count,
                    ];
                })
                ->values()
                ->toArray();
        @endphp

        <x-data-table :data="$rows" :title="__('New enrolments counts by sector')" :columns="[
            [
                'key' => 'QetaaName',
                'label' => __('Sector name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
                'filter' => true,
            ],
            [
                'key' => 'Count',
                'label' => __('Current count'),
                'type' => 'label',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
        ]" :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
