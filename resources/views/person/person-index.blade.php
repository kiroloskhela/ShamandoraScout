@extends('layouts.app', ['pageTitle' => __('Control data') ?? ''])


@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-table-server-search :q="$q ?? ''" placeholder="{{ __('Name / code / phone / sector') }}" />

        <x-data-table :data="$persons->items()" title="{{ __('Manage users') }}" :add-button="[
            'label' => __('Add user'),
            'route' => route('person.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :header-buttons="[
            [
                'label' => __('Download Excel'),
                'route' => route('export.scouts.excel', auth()->id()),
                'cssClass' =>
                    'bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
            ],
        ]" :columns="[
            [
                'key' => 'PersonID',
                'label' => __('User ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
            [
                'key' => 'full_name',
                'label' => __('Full name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 dark:text-blue-300 font-bold text-sm',
            ],
            [
                'key' => 'PersonPersonalMobileNumber',
                'label' => __('Phone number'),
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
                'key' => 'QetaaName',
                'label' => __('Sector'),
                'type' => 'label',
                'filter' => true,
                'cssClass' => 'text-blue-600 dark:text-blue-300 font-bold text-sm',
            ],
            [
                'key' => 'HasAnsweredQuestions',
                'label' => __('Answered questions'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 dark:text-blue-300 font-bold text-sm',
            ],
        ]"
            :actions="[
                [
                    'name' => 'edit',
                    'label' => __('Edit'),
                    'route' => route('person.edit', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'delete',
                    'label' => __('Delete'),
                    'route' => route('person.delete', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
                ],
                [
                    'name' => 'show',
                    'label' => __('View'),
                    'route' => route('person.show', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200',
                ],
            ]" :searchable="false" :sortable="true" :pagination="false" :per-page="25"
            :server-filters="true"
            :filter-options="$filterOptions ?? []"
            :active-server-filters="$activeServerFilters ?? []" />
        <div class="mt-4">
            {{ $persons->links() }}
        </div>
    </div>
@endsection
