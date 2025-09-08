@extends('layouts.app' , ['pageTitle' => "إدارة المجموعات" ?? ''])


@section('content')
<div class="container mx-auto px-4 py-8">
    <x-data-table :data="$groups" title="إدارة المجموعات" :add-button="[
            'label' => 'إضافة مجموعة جديدة',
            'route' => route('group.create'),
            'cssClass' => 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200'
        ]" :columns="[
            [
                'key' => 'GroupID1',
                'label' => 'رقم المجموعة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium'
            ],
            [
                'key' => 'GroupTypeName',
                'label' => 'اسم نوع المجموعة',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm'
            ]
            ,
            [
                'key' => 'IncludedUnderGroupID',
                'label' => 'رقم المجموعة الفرعية',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm'
            ]
            ,
            [
                'key' => 'IncludedUnderGroupName',
                'label' => 'اسم المجموعة الفرعية',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm'
            ]

        ]" :actions="[
            [
                'name' => 'edit',
                'label' => 'تعديل',
                'route' => route('group.edit', ':id'),
                'idField' => 'GroupID1',
                'cssClass' => 'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2'
            ],
            [
                'name' => 'delete',
                'label' => 'مسح',
                'route' => route('group.delete', ':id'),
                'idField' => 'GroupID1',
                'cssClass' => 'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200'
            ]
        ]" :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
</div>
@endsection