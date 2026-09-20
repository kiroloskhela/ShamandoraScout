@extends('layouts.app', ['pageTitle' => __('Assign scout scarves')])

@php
    $folarOptions = collect($folars ?? [])->map(fn ($folar) => [
        'value' => $folar->FolarID,
        'label' => $folar->FolarName,
    ])->values()->all();

    $hiddenFields = array_filter([
        'q' => request('q'),
        'page' => request('page'),
        'f[SanaMarhalaName]' => $activeServerFilters['SanaMarhalaName'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm text-center">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <x-data-table :data="$persons" title="{{ __('Assign scout scarves') }}" :header-buttons="[
            [
                'label' => __('Members data'),
                'route' => route('person.index', ['id' => Auth::id()]),
                'cssClass' =>
                    'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-4 rounded-lg transition-colors duration-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-100',
            ],
        ]" :columns="[
            [
                'key' => 'photo_url',
                'label' => __('Personal photo'),
                'type' => 'image',
                'alt' => __('Personal photo'),
                'sortable' => false,
            ],
            [
                'key' => 'full_name',
                'label' => __('Full name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 dark:text-blue-300 font-bold text-sm',
            ],
            [
                'key' => 'QetaaName',
                'label' => __('Sector'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 dark:text-blue-300 font-bold text-sm',
            ],
            [
                'key' => 'SanaMarhalaName',
                'label' => __('Stage'),
                'type' => 'label',
                'filter' => true,
                'cssClass' => 'text-blue-600 dark:text-blue-300 font-bold text-sm',
            ],
            [
                'key' => 'FolarID',
                'label' => __('Scout scarf'),
                'type' => 'select',
                'sortable' => false,
                'namePrefix' => 'folar',
                'idField' => 'PersonID',
                'saveUrl' => route('person.folar.sync'),
                'emptyLabel' => __('No scout scarf'),
                'options' => $folarOptions,
                'hiddenFields' => $hiddenFields,
            ],
        ]"
            :searchable="true"
            :sortable="true"
            :pagination="true"
            :per-page="25"
            :server-search="true"
            :server-filters="true"
            :filter-options="$filterOptions ?? []"
            :active-server-filters="$activeServerFilters ?? []" />
    </div>
@endsection
