@extends('layouts.app', ['pageTitle' => 'المراحل الدراسية مفصله'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$sana->toArray()" title=" إدارة المراحل الدراسية مفصله" :add-button="[
            'label' => 'إضافة مرحلة دراسية',
            'route' => route('sana-marhala.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'SanaMarhalaID',
                'label' => 'رقم المرحلة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'SanaMarhalaName',
                'label' => 'اسم المرحلة',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
        ]"
            :actions="[
                [
                    'name' => 'edit',
                    'label' => 'تعديل',
                    'route' => route('sana-marhala.edit', ':id'),
                    'idField' => 'SanaMarhalaID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
                ],
                [
                    'name' => 'delete',
                    'label' => 'مسح',
                    'route' => route('sana-marhala.delete', ':id'),
                    'idField' => 'SanaMarhalaID',
                    'cssClass' =>
                        'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
                ],
            ]" :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
