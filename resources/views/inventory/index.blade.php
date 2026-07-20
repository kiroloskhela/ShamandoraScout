@extends('layouts.app', ['pageTitle' => __('Inventory')])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$inventory->toArray()" :title="__('Manage inventory')" :add-button="[
            'label' => __('Add new item'),
            'route' => route('inventory.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'InventoryID',
                'label' => __('Item ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 dark:text-slate-100 font-medium',
            ],
            [
                'key' => 'ItemName',
                'label' => __('Item name'),
                'type' => 'label',
                'cssClass' => 'text-blue-600 dark:text-blue-300 font-bold text-sm',
            ],
            [
                'key' => 'ItemQuantity',
                'label' => __('Quantity'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800 dark:text-slate-200',
            ],
            [
                'key' => 'ItemMeasuringUnit',
                'label' => __('Unit of measure'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800 dark:text-slate-200',
            ],
            [
                'key' => 'Category',
                'label' => __('Category'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800 dark:text-slate-200',
            ],
            [
                'key' => 'Location',
                'label' => __('Location'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800 dark:text-slate-200',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('inventory.edit', ':id'),
                'idField' => 'InventoryID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('inventory.delete', ':id'),
                'idField' => 'InventoryID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
