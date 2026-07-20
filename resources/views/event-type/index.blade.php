@extends('layouts.app', ['pageTitle' => __('Occasions (page)')])


@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$eventTypes->toArray()" :title="__('Manage occasions')" :add-button="[
            'label' => __('Add new faculty (2)'),
            'route' => route('event-type.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'EventTypeID',
                'label' => __('Occasion ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'EventTypeName',
                'label' => __('Occasion name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'TakesReservationLabel',
                'label' => __('Takes reservation'),
                'type' => 'label',
                'cssClass' => 'text-sm font-semibold text-slate-700',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('event-type.edit', ':id'),
                'idField' => 'EventTypeID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('event-type.delete', ':id'),
                'idField' => 'EventTypeID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
