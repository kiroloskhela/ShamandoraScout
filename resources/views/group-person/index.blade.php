@extends('layouts.app', ['pageTitle' => __('Manage group members')])


@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$groupPersons" :title="__('Manage group members')" :add-button="[
            'label' => __('Add new person (2)'),
            'route' => route('group-person.create-khadem'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'ShamandoraCode',
                'label' => __('Shamandora code (2)'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'PersonFullName',
                'label' => __('Person name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'GroupRoleName',
                'label' => __('Role name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'GroupID',
                'label' => __('Group ID'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'GroupDetails',
                'label' => __('Group name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
        ]"
            :actions="[
                [
                    'name' => 'edit',
                    'label' => __('Edit'),
                    'route' => route('group-person.edit', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'delete',
                    'label' => __('Delete'),
                    'route' => route('group-person.delete', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
                ],
            ]" :searchable="true" :sortable="true" :pagination="true" :per-page="25" />
    </div>
@endsection
