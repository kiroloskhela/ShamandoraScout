@extends('layouts.app', ['pageTitle' => 'إدارة الحالات الخاصة'])

@section('content')
    <div class="container mx-auto px-4 py-8">
                <x-table-server-search :q="$q ?? ''" />

        <x-data-table :data="$cases->items()" title="إدارة الحالات الخاصة" :add-button="[
            'label' => 'إضافة حالة خاصة',
            'route' => route('personspecialcase.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'SpecialCaseID',
                'label' => 'رقم الحالة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'PersonName',
                'label' => __('Person'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'PersonID',
                'label' => __('Person ID'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'ServentName',
                'label' => 'بواسطة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'CaseDate',
                'label' => __('Date'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'Note',
                'label' => __('Note'),
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => __('Edit'),
                'route' => route('personspecialcase.edit', ':id'),
                'idField' => 'SpecialCaseID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => __('Delete'),
                'route' => route('personspecialcase.delete', ':id'),
                'idField' => 'SpecialCaseID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="false" :sortable="true" :pagination="false" :per-page="10" />
    </div>
        <div class="mt-4">
            {{ $cases->links() }}
        </div>
@endsection
