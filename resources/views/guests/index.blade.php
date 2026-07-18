@extends('layouts.app', ['pageTitle' => 'إدارة الضيوف'])

@section('content')
    <div class="container mx-auto px-4 py-8">
                <x-table-server-search :q="$q ?? ''" />

        <x-data-table :data="$guests->items()" title="إدارة الضيوف" :add-button="[
            'label' => 'إضافة ضيف',
            'route' => route('guests.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'GuestID',
                'label' => '#',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-700 font-medium',
            ],
            [
                'key' => 'FullName',
                'label' => __('Name'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'MobileNumber',
                'label' => __('Mobile number'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'RaqamQawmy',
                'label' => __('National ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900',
            ],
            [
                'key' => 'PersonFullName',
                'label' => __('Linked person'),
                'type' => 'text',
                'cssClass' => 'text-sm text-blue-700 font-medium',
            ],
        ]" :actions="[
            [
                'name' => 'show',
                'label' => __('View'),
                'route' => route('guests.show', ':id'),
                'idField' => 'GuestID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 ml-2',
            ],
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('guests.edit', ':id'),
                'idField' => 'GuestID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('guests.delete', ':id'),
                'idField' => 'GuestID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700',
            ],
        ]"
            :searchable="false" :sortable="true" :pagination="false" :per-page="10" />
    </div>
        <div class="mt-4">
            {{ $guests->links() }}
        </div>
@endsection
