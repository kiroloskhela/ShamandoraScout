@extends('layouts.app', ['pageTitle' => __('Occasions')])


@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$events" title="{{ __('Manage occasions') }}" :add-button="[
            'label' => __('Add new occasion'),
            'route' => route('event.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'EventID',
                'label' => __('Occasion ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
            [
                'key' => 'EventTypeName',
                'label' => __('Occasion type'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 dark:text-blue-300 font-bold text-sm',
                'filter' => true,
            ],
            [
                'key' => 'EventName',
                'label' => __('Occasion name'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
            [
                'key' => 'EventStartDate',
                'label' => __('Occasion start date'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
            [
                'key' => 'EventEndDate',
                'label' => __('Occasion end date'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
            [
                'key' => 'EventQetaat',
                'label' => __('Sectors'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
                'filter' => true,
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('event.edit', ':id'),
                'idField' => 'EventID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('event.delete', ':id'),
                'idField' => 'EventID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
