@extends('layouts.app', ['pageTitle' => 'تسجيل درجات الامتحان'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        @if (session('status'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 text-sm text-center">
                {{ session('status') }}
            </div>
        @endif

        <x-data-table :data="$marks" title="تسجيل درجات الامتحان" :add-button="[
            'label' => 'تسجيل درجة جديدة',
            'route' => route('personexammark.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'ExamMarkID',
                'label' => '#',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'PersonName',
                'label' => 'المخدوم',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'QetaaName',
                'label' => 'القطعة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'SanaMarhalaName',
                'label' => 'سنة المرحلة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'TheoreticalMark',
                'label' => 'نظري',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'PracticalMark',
                'label' => 'عملي',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'ExamDate',
                'label' => 'تاريخ الامتحان',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'ServentName',
                'label' => 'سجّلها',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'Note',
                'label' => 'ملاحظة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => 'تعديل',
                'route' => route('personexammark.edit', ':id'),
                'idField' => 'ExamMarkID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => 'مسح',
                'route' => route('personexammark.delete', ':id'),
                'idField' => 'ExamMarkID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
