@extends('layouts.app' , ['pageTitle' => __('Link leaders to duties (alt)')])


@section('content')
<div class="container mx-auto px-4 py-8">
    <x-data-table :data="$personRoles" :title="__('Manage leader role assignments')" :add-button="[
            'label' => __('Link leader to roles'),
            'route' => route('person-role.create'),
            'cssClass' => 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200'
        ]" :columns="[
            [
                'key' => 'PersonRoleID',
                'label' => __('Sequence'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900'
            ],
            [
                'key' => 'ShamandoraCode',
                'label' => __('Shamandora code'),
                'type' => 'text',
                'cssClass' => 'text-blue-600 font-bold text-sm'
            ],
            [
                'key' => 'PersonFullName',
                'label' => __('Leader name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm'
            ],
            [
                'key' => 'RoleName',
                'label' => __('Role name (System Role)'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm'
            ]
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('person-role.edit', ':id'),
                'idField' => 'PersonRoleID',
                'cssClass' => 'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2'
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('person-role.delete', ':id'),
                'idField' => 'PersonRoleID',
                'cssClass' => 'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200'
            ]
        ]" :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
</div>
@endsection