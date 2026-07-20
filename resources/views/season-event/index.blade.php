@extends('layouts.app', ['pageTitle' => __('Link season to event')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$seasonEvents" :title="__('Manage season-event links')" :add-button="[
            'label' => __('Add new link'),
            'route' => route('season-event.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'SeasonEventID',
                'label' => __('Season-event ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
            [
                'key' => 'SeasonName',
                'label' => __('Season name'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
                'filter' => true,
            ],
            [
                'key' => 'SeasonYear',
                'label' => __('Year'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
                'filter' => true,
            ],
            [
                'key' => 'EventTypeName',
                'label' => __('Event type'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
                'filter' => true,
            ],
            [
                'key' => 'EventName',
                'label' => __('Event name'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
            [
                'key' => 'EventStartDate',
                'label' => __('Event start date'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
            [
                'key' => 'EventEndDate',
                'label' => __('Event end date'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('season-event.edit', ':id'),
                'idField' => 'SeasonEventID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('season-event.delete', ':id'),
                'idField' => 'SeasonEventID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
