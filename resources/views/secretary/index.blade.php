@extends('layouts.app', ['pageTitle' => 'محضر الاجتماعات'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$documents" title="إدارة الوثائق" :add-button="[
            'label' => 'إضافة وثيقة جديدة',
            'route' => route('secretary.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'DocumentID',
                'label' => 'رقم المستند',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'DocumentName',
                'label' => 'اسم المستند',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
        ]" :actions="[
            [
                'name' => 'download',
                'label' => 'تحميل',
                'route' => route('secretary.download', ':id'),
                'idField' => 'DocumentID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('secretary.edit', ':id'),
                'idField' => 'DocumentID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('secretary.delete', ':id'),
                'idField' => 'DocumentID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
