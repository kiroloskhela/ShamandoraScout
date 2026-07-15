@extends('layouts.app', ['pageTitle' => 'المراحل الدراسية'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$marhala->toArray()" title="إدارة المراحل الدراسية" :add-button="[
            'label' => 'إضافة مرحلة دراسية',
            'route' => route('marhala.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'MarhalaID',
                'label' => 'رقم المرحلة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'MarhalaName',
                'label' => 'اسم  المرحلة',
                'type' => 'label',
                'cssClass' => 'text-blue-600 font-bold text-sm',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('marhala.edit', ':id'),
                'idField' => 'MarhalaID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('marhala.delete', ':id'),
                'idField' => 'MarhalaID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
