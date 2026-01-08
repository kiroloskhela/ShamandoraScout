@extends('layouts.app', ['pageTitle' => 'اقسام المناهج'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$curricula" title="إدارة المناهج" :add-button="[
            'label' => 'إضافة محاضرة جديدة',
            'route' => route('curricula.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'CurriculaID',
                'label' => 'رقم محاضرة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'CurriculaName',
                'label' => 'اسم محاضرة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'MarhalaName',
                'label' => 'المرحلة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'CurriculaCategoryName',
                'label' => 'التصنيف',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
        ]" :actions="[
            [
                'name' => 'download',
                'label' => 'تحميل',
                'route' => route('curricula.download', ':id'),
                'idField' => 'CurriculaID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'edit',
                'label' => 'تعديل',
                'route' => route('curricula.edit', ':id'),
                'idField' => 'CurriculaID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => 'مسح',
                'route' => route('curricula.delete', ':id'),
                'idField' => 'CurriculaID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
