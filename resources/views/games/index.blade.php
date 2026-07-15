@extends('layouts.app', ['pageTitle' => __('Games')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$games->toArray()" title="{{ __('Manage games') }}" :add-button="[
            'label' => __('Add new game'),
            'route' => route('games.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'GameID',
                'label' => __('Game ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'Title',
                'label' => __('Game name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'AgeGroup',
                'label' => __('Age group'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-700',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('games.edit', ':id'),
                'idField' => 'GameID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('games.delete', ':id'),
                'idField' => 'GameID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
            [
                'name' => 'show',
                'label' => __('View'),
                'route' => route('games.show', ':id'),
                'idField' => 'GameID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 ml-2',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
