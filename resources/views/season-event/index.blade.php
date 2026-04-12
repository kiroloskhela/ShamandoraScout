@extends('layouts.app', ['pageTitle' => ' ربط موسم بفعالية'])

@section('content')
    <div class="container mx-auto px-4 py-8">
        <x-data-table :data="$seasonEvents" title="إدارة ربط موسم بفعالية" :add-button="[
            'label' => 'إضافة ربط جديد',
            'route' => route('season-event.create'),
            'cssClass' =>
                'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200',
        ]" :columns="[
            [
                'key' => 'SeasonEventID',
                'label' => 'رقم موسم-فعالية',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'SeasonName',
                'label' => 'اسم الموسم',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'SeasonYear',
                'label' => 'السنة',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'EventTypeName',
                'label' => 'نوع الحدث',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'EventName',
                'label' => 'اسم الحدث',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'EventStartDate',
                'label' => 'تاريخ بدء الحدث',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
            [
                'key' => 'EventEndDate',
                'label' => 'تاريخ انتهاء الحدث',
                'type' => 'text',
                'cssClass' => 'text-sm text-gray-900 font-medium',
            ],
        ]" :actions="[
            [
                'name' => 'edit',
                'label' => 'تعديل',
                'route' => route('season-event.edit', ':id'),
                'idField' => 'SeasonEventID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 ml-2',
            ],
            [
                'name' => 'delete',
                'label' => 'مسح',
                'route' => route('season-event.delete', ':id'),
                'idField' => 'SeasonEventID',
                'cssClass' =>
                    'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200',
            ],
        ]"
            :searchable="true" :sortable="true" :pagination="true" :per-page="10" />
    </div>
@endsection
