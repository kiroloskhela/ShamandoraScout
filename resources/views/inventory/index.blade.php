@extends('layouts.app', ['pageTitle' => 'العهده' ?? ''])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$inventory->toArray()" title="إدارة العهده" :add-button="[
            'label' => 'إضافة عنصر جديد',
            'route' => route('inventory.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'InventoryID',
                'label' => 'رقم العنصر',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'ItemName',
                'label' => 'اسم العنصر',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'ItemQuantity',
                'label' => __('Quantity'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'ItemMeasuringUnit',
                'label' => 'وحدة القياس',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'Category',
                'label' => 'الفئة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
            ],
            [
                'key' => 'Location',
                'label' => __('Location'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-800',
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
