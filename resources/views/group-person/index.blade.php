@extends('layouts.app', ['pageTitle' => 'إدارة الأشخاص في المجموعة' ?? ''])


@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$groupPersons->items()" title="إدارة الأشخاص في المجموعة" :add-button="[
            'label' => 'إضافة شخص  جديد',
            'route' => route('group-person.create-khadem'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'ShamandoraCode',
                'label' => 'رقم الشمندورة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'PersonFullName',
                'label' => 'اسم الشخص',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'GroupRoleName',
                'label' => 'اسم الدور',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'GroupID',
                'label' => 'رقم المجموعة',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
            [
                'key' => 'GroupDetails',
                'label' => 'اسم المجموعة',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
        ]"
            :actions="[
                [
                    'name' => 'edit',
                    'label' => 'تعديل',
                    'route' => route('group-person.edit', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'delete',
                    'label' => 'مسح',
                    'route' => route('group-person.delete', ':id'),
                    'idField' => 'PersonID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
                ],
            ]" :searchable="true" :sortable="true" :pagination="false" :per-page="25" />
        <div class="mt-4">
            {{ $groupPersons->links() }}
        </div>
    </div>
@endsection
